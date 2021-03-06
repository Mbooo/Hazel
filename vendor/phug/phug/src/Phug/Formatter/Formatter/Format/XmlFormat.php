<?php

namespace Phug\Formatter\Format;

use Generator;
use Phug\Formatter;
use Phug\Formatter\AbstractFormat;
use Phug\Formatter\AssignmentContainerInterface;
use Phug\Formatter\Element\AbstractValueElement;
use Phug\Formatter\Element\AssignmentElement;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\Element\MixinCallElement;
use Phug\Formatter\Element\TextElement;
use Phug\Formatter\ElementInterface;
use Phug\Formatter\MarkupInterface;
use Phug\Formatter\Partial\AssignmentHelpersTrait;
use Phug\FormatterException;
use Phug\Util\AttributesInterface;
use Phug\Util\Joiner;
use SplObjectStorage;

class XmlFormat extends AbstractFormat
{
    use AssignmentHelpersTrait;

    const DOCTYPE = '<?xml version="1.0" encoding="utf-8" ?>';
    const OPEN_PAIR_TAG = '<%s>';
    const CLOSE_PAIR_TAG = '</%s>';
    const SELF_CLOSING_TAG = '<%s />';
    const ATTRIBUTE_PATTERN = ' %s="%s"';
    const BOOLEAN_ATTRIBUTE_PATTERN = ' %s="%s"';
    const BUFFER_VARIABLE = '$__value';

    public function __construct(Formatter $formatter = null)
    {
        parent::__construct($formatter);

        $this
            ->setOptionsDefaults([
                'attributes_mapping'    => [],
                'assignment_handlers'   => [],
                'attribute_assignments' => [],
            ])
            ->registerHelper('available_attribute_assignments', [])
            ->addPatterns([
                'open_pair_tag'             => static::OPEN_PAIR_TAG,
                'close_pair_tag'            => static::CLOSE_PAIR_TAG,
                'self_closing_tag'          => static::SELF_CLOSING_TAG,
                'attribute_pattern'         => static::ATTRIBUTE_PATTERN,
                'boolean_attribute_pattern' => static::BOOLEAN_ATTRIBUTE_PATTERN,
                'save_value'                => static::SAVE_VALUE,
                'buffer_variable'           => static::BUFFER_VARIABLE,
            ])
            ->provideAttributeAssignments()
            ->provideAttributeAssignment()
            ->provideStandAloneAttributeAssignment()
            ->provideMergeAttributes()
            ->provideArrayEscape()
            ->provideAttributesAssignment()
            ->provideClassAttributeAssignment()
            ->provideStandAloneClassAttributeAssignment()
            ->provideStyleAttributeAssignment()
            ->provideStandAloneStyleAttributeAssignment();

        $handlers = $this->getOption('attribute_assignments');
        foreach ($handlers as $name => $handler) {
            $this->addAttributeAssignment($name, $handler);
        }
    }

    protected function addAttributeAssignment($name, $handler)
    {
        $availableAssignments = $this->getHelper('available_attribute_assignments');
        $this->registerHelper($name.'_attribute_assignment', $handler);
        $availableAssignments[] = $name;

        return $this->registerHelper('available_attribute_assignments', $availableAssignments);
    }

    public function requireHelper($name)
    {
        $provider = $this->formatter
            ->getDependencies()
            ->getProvider(
                $this->helperName('available_attribute_assignments')
            );
        $required = $provider->isRequired();

        parent::requireHelper($name);

        if (!$required && $provider->isRequired()) {
            foreach ($this->getHelper('available_attribute_assignments') as $assignment) {
                $this->requireHelper($assignment.'_attribute_assignment');
            }
        }

        return $this;
    }

    public function __invoke(ElementInterface $element)
    {
        return $this->format($element);
    }

    protected function isSelfClosingTag(MarkupInterface $element, $isSelfClosing = null)
    {
        if (is_null($isSelfClosing)) {
            $isSelfClosing = $element->isAutoClosed();
        }

        if ($isSelfClosing && $element->hasChildren()) {
            $visibleChildren = array_filter($element->getChildren(), function ($child) {
                return $child && (
                    !($child instanceof TextElement) ||
                    trim($child->getValue()) !== ''
                );
            });
            if (count($visibleChildren) > 0) {
                $this->throwException(
                    $element->getName().' is a self closing element: '.
                    '<'.$element->getName().'/> but contains nested content.',
                    $element
                );
            }
        }

        return $isSelfClosing;
    }

    protected function isBlockTag(MarkupInterface $element)
    {
        return true;
    }

    public function isWhiteSpaceSensitive(MarkupInterface $element)
    {
        return false;
    }

    protected function hasNonStaticAttributes(MarkupInterface $element)
    {
        if ($element instanceof MarkupElement || $element instanceof MixinCallElement) {
            foreach ($element->getAttributes() as $attribute) {
                if ($attribute->hasStaticMember('value')) {
                    continue;
                }
                if ($attribute->getValue() instanceof ExpressionElement &&
                    $attribute->getValue()->hasStaticMember('value')) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    protected function formatAttributeElement(AttributeElement $element)
    {
        $value = $element->getValue();
        $name = $element->getName();
        $nonEmptyAttribute = ($name === 'class' || $name === 'id');
        if ($nonEmptyAttribute && (
            !$value ||
            ($value instanceof TextElement && ((string) $value->getValue()) === '') ||
            (is_string($value) && in_array(trim($value), ['', '""', "''"]))
        )) {
            return '';
        }
        if ($value instanceof ExpressionElement) {
            if ($nonEmptyAttribute && in_array(trim($value->getValue()), ['', '""', "''"])) {
                return '';
            }
            if (strtolower($value->getValue()) === 'true') {
                $formattedValue = null;
                if ($name instanceof ExpressionElement) {
                    $bufferVariable = $this->pattern('buffer_variable');
                    $name = $this->pattern(
                        'php_display_code',
                        $this->pattern(
                            'save_value',
                            $bufferVariable,
                            $this->formatCode($name->getValue(), $name->isChecked())
                        )
                    );
                    $value = new ExpressionElement($bufferVariable);
                    $formattedValue = $this->format($value);
                }
                $formattedName = $this->format($name);
                $formattedValue = $formattedValue || $formattedValue === '0'
                    ? $formattedValue
                    : $formattedName;

                return $this->pattern(
                    'boolean_attribute_pattern',
                    $formattedName,
                    $formattedValue
                );
            }
            if (in_array(strtolower($value->getValue()), ['false', 'null', 'undefined'])) {
                return '';
            }
        }

        return $this->pattern(
            'attribute_pattern',
            $this->format($name),
            $this->format($value)
        );
    }

    protected function formatPairTagChildren(MarkupElement $element)
    {
        $firstChild = $element->getChildAt(0);
        $needIndent = (
            (
                (
                    $firstChild instanceof CodeElement &&
                    $this->isBlockTag($element)
                ) || (
                    $firstChild instanceof MarkupInterface &&
                    $this->isBlockTag($firstChild)
                )
            ) &&
            !$this->isWhiteSpaceSensitive($element)
        );

        return sprintf(
            $needIndent
                ? $this->getNewLine().'%s'.$this->getIndent()
                : '%s',
            $this->formatElementChildren($element)
        );
    }

    protected function formatPairTag($open, $close, MarkupElement $element)
    {
        return $this->pattern(
            'pair_tag',
            $open,
            $element->hasChildren()
                ? $this->formatPairTagChildren($element)
                : '',
            $close
        );
    }

    /**
     * @param AssignmentElement $element
     *
     * @throws FormatterException
     *
     * @return iterable
     */
    protected function yieldAssignmentElement(AssignmentElement $element)
    {
        foreach ($this->getOption('assignment_handlers') as $handler) {
            $iterator = $handler($element) ?: [];

            foreach ($iterator as $newElement) {
                yield $newElement;
            }
        }

        /* @var MarkupElement $markup */
        $markup = $element->getContainer();

        $arguments = $markup instanceof AssignmentContainerInterface
            ? $this->formatAttributeAssignments($markup)
            : [];

        $arguments = array_merge(
            $markup instanceof AttributesInterface
                ? $this->formatMarkupAttributes($markup)
                : [],
            $arguments
        );

        foreach ($markup->getAssignments() as $assignment) {
            /* @var AssignmentElement $assignment */
            $this->throwException(
                'Unable to handle '.$assignment->getName().' assignment',
                $assignment
            );
        }

        if (count($arguments)) {
            yield $this->attributesAssignmentsFromPairs($arguments);
        }
    }

    /**
     * @param AssignmentContainerInterface $markup
     *
     * @return array<string>
     */
    protected function formatAttributeAssignments(AssignmentContainerInterface $markup)
    {
        $arguments = [];

        foreach ($this->yieldAssignmentAttributes($markup) as $attribute) {
            $checked = method_exists($attribute, 'isChecked') && $attribute->isChecked();

            while (method_exists($attribute, 'getValue')) {
                $attribute = $attribute->getValue();
            }

            $arguments[] = $this->formatCode($attribute, $checked);
        }

        return $arguments;
    }

    /**
     * @param AssignmentContainerInterface $markup
     *
     * @return Generator|AbstractValueElement[]
     */
    protected function yieldAssignmentAttributes(AssignmentContainerInterface $markup)
    {
        foreach ($markup->getAssignmentsByName('attributes') as $attributesAssignment) {
            /* @var AssignmentElement $attributesAssignment */
            foreach ($attributesAssignment->getAttributes() as $attribute) {
                /* @var AbstractValueElement $attribute */
                yield $attribute;
            }

            $markup->removedAssignment($attributesAssignment);
        }
    }

    /**
     * @param AttributesInterface $markup
     *
     * @return array<string>
     */
    protected function formatMarkupAttributes(AttributesInterface $markup)
    {
        $arguments = [];
        $attributes = $markup->getAttributes();

        foreach ($attributes as $attribute) {
            /* @var AttributeElement $attribute */
            $arguments[] = $this->formatAttributeAsArrayItem($attribute);
        }

        $attributes->removeAll($attributes);

        return $arguments;
    }

    /**
     * @param AssignmentElement $element
     *
     * @throws FormatterException
     *
     * @return string
     */
    protected function formatAssignmentElement(AssignmentElement $element)
    {
        return (new Joiner($this->yieldAssignmentElement($element)))->mapAndJoin([$this, 'format'], '');
    }

    protected function hasDuplicateAttributeNames(MarkupInterface $element)
    {
        if ($element instanceof MarkupElement || $element instanceof MixinCallElement) {
            $names = [];
            foreach ($element->getAttributes() as $attribute) {
                $name = $attribute->getName();
                if (($name instanceof ExpressionElement && !$name->hasStaticValue()) || in_array($name, $names)) {
                    return true;
                }

                $names[] = $name;
            }
        }

        return false;
    }

    protected function formatAttributes(MarkupElement $element)
    {
        if ($this->hasNonStaticAttributes($element) ||
            $this->hasDuplicateAttributeNames($element)) {
            $empty = true;
            foreach ($element->getAssignmentsByName('attributes') as $attribute) {
                $empty = false;
                break;
            }
            if ($empty) {
                $data = new SplObjectStorage();
                $data->attach(new ExpressionElement('[]'));
                $element->addAssignment(new AssignmentElement('attributes', $data, $element));
            }
        }

        foreach ($element->getAssignments() as $assignment) {
            return $this->format($assignment);
        }

        $code = '';

        foreach ($element->getAttributes() as $attribute) {
            $code .= $this->format($attribute);
        }

        return $code;
    }

    protected function formatMarkupElement(MarkupElement $element)
    {
        $tag = $this->format($element->getName());
        $saveAttributes = clone $element->getAttributes();
        $saveAssignments = clone $element->getAssignments();
        $attributes = $this->formatAttributes($element);
        $dirtyAttributes = $element->getAttributes();
        $dirtyAttributes->removeAll($dirtyAttributes);
        $dirtyAttributes->addAll($saveAttributes);
        $dirtyAssignments = $element->getAssignments();
        $dirtyAssignments->removeAll($dirtyAssignments);
        $dirtyAssignments->addAll($saveAssignments);

        $tag = $this->isSelfClosingTag($element)
            ? $this->pattern(
                $element->isAutoClosed() && $this->hasPattern('explicit_closing_tag')
                    ? 'explicit_closing_tag'
                    : 'self_closing_tag',
                $tag.$attributes
            )
            : $this->formatPairTag(
                $this->pattern('open_pair_tag', $tag.$attributes),
                $this->pattern('close_pair_tag', $tag),
                $element
            );

        return !$element->isAutoClosed() && $this->isBlockTag($element)
            ? $this->getIndent().$tag.$this->getNewLine()
            : $tag;
    }
}
