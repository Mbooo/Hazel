$(document).ready(function () {
  $(".ui.dropdown").dropdown();
  $("#message-when-reserved").hide();
  $("#message-when-password-changed").hide();
  $("#message-when-informations-changed").hide();
  $("#message-when-confirmation-done").hide();




  $("#embedpollfileinput").change(function(e){
    const $FILES_INPUT = $("#upload_files_portfolio");
    img = $FILES_INPUT.form("get values");
    console.log(img);
  });


  /**             PROFILE FORMS                      */

  // $("input:text").click(function() {
  //   $(this).parent().find("input:file").click();
  // });
  //
  // $('input:file', '.ui.action.input')
  //     .on('change', function(e) {
  //       var name = e.target.files[0].name;
  //       $('input:text', $(e.target).parent()).val(name);
  //     });



  /** Informations form handler */

  const $INFORMATIONS_FORM = $("#informationsModificationForm");

  $INFORMATIONS_FORM.form({
    fields:{
      name:{
        identifier:"name",
        rules:[
          {
            type:"empty",
            prompt:"Veuillez saisir un nom d'utilisateur",
          }
        ],
      },
      description:{
        identifier:"description",
        rules:[
          {
            type:"empty",
            prompt:"Veuillez saisir une description",
          },
        ],
      },
      dateOfBirth:{
        identifier:"dateOfBirth",
        rules:[
          {
            type:"empty",
            prompt:"Veuillez saisir une date de naissance",
          },
        ],
      },
      place:{
        identifier:"place",
        rules:[
          {
            type:"empty",
            prompt:"Veuillez saisir une adresse",
          },
        ],
      },
      phone:{
        identifier:"phone",
        rules:[
          {
            type:"empty",
            prompt:"Veuillez saisir un numéro de téléphone",
          },
          {
            type:"regExp[^(?:(?:\\+|00)33|0)\\s*[1-9](?:[\\s.-]*\\d{2}){4}$]",
            prompt:"Votre numéro de téléphone n'est pas valide",
          },
        ],
      },
    },
  });


  $("#submitInformations").click(function (e){
    if(!$INFORMATIONS_FORM.form("is valid")){
      return;
    }

    e.preventDefault();

    allFields = $INFORMATIONS_FORM.form("get values");
    $.ajax({
      type:"POST",
      url:"/api/changeInformations",
      data:allFields,
      dataType:"json",

      success:function (response){
        console.log(response)
        if(response.success){
          $("#informationsModificationForm").hide();
          $("#message-when-informations-changed").show();
        }else{
          $INFORMATIONS_FORM.form("add errors", [response.message]);
        }
      },
    });
  });



  /** Password form handler */

  const $PASSWORD_FORM = $("#passwordModificationForm");

  $PASSWORD_FORM.form({
    fields:{
      oldpassword:{
        identifier:"oldpassword",
        rules:[
          {
            type:"empty",
            prompt:"Veuillez saisir votre mot de passe actuel",
          },

        ],
  },
      newpassword:{
        identifier:"newpassword",
        rules:[
          {
            type:"empty",
            prompt:"Veuillez saisir votre nouveau mot de passe"
          },
          {
            type: "minLength[6]",
            prompt: "Votre mot de passe doit contenir au moins {ruleValue} caractères",
          },
          {
            type:"different[oldpassword]",
            prompt: "Le nouveau mot de passe ne doit pas correspondre à l'actuel",

          },

        ],
      },

      confirmPassword:{
        identifier:"confirmPassword",
        rules:[
          {
            type:"match[newpassword]",
            prompt: "Les mots de passe ne correspondent pas",

          },
        ],
      },
    },
  });

  $("#submitPassword").click(function (e){
    if(!$PASSWORD_FORM.form("is valid")){
      return;
    }

    e.preventDefault();

    allFields = $PASSWORD_FORM.form("get values");
    $.ajax({
      type: "POST",
      url: "/api/changePassword",
      data: allFields,
      dataType: "json",
      success: function (response) {
        console.log(response);

        if (response.success) {
          $("#passwordModificationForm").hide();
          $("#message-when-password-changed").show();

        }else{
          $("#passwordModificationForm").form("add errors",[response.message]);
        }

      },
    });
  });



  /** CALENDAR FOR RESERVATION OF APPOINTMENT */
  const today = new Date();

  $("#calendarBirthday").calendar({
    type: 'date',
    text: {
      days: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
      months: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Decembre'],
      monthsShort: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'],
      today: 'Aujourd\'hui',
      now: 'Maintenant',
    },
    minDate: new Date(today.getFullYear() - 50, today.getMonth(), today.getDate()),
    maxDate: new Date(
        today.getFullYear() + 1,
        today.getMonth(),
        today.getDate(),
    ),
    formatter: {
      date: function (date, settings) {
        if (!date) return "";
        var day = date.getDate();
        var month = date.getMonth() + 1;
        var year = date.getFullYear();
        return year + "-" + month + "-" + day;
      },
    },
  });
  /** Register form handler */

  const $REGISTER_FORM = $("#registerForm");

  $REGISTER_FORM.form({
    fields: {
      name: {
        identifier: "name",
        rules: [
          {
            type: "empty",
            prompt: "Veuillez renseigner votre nom",
          },

        ],
      },
      role: {
        identifier: "role",
        rules: [
          {
            type: "minCount[1]",
            prompt:
                "Veuillez nous dire quel type de compte vous souhaitez créer",
          },
        ],
      },
      username: {
        identifier: "username",
        rules: [
          {
            type: "empty",
            prompt: "Veuillez saisir un nom d'utilisateur",
          },
        ],
      },
      email: {
        identifier: "email",
        rules: [
          {
            type: "email",
            prompt: "Veuillez saisir un email valide",
          },
        ],
      },
      password: {
        identifier: "password",
        rules: [
          {
            type: "empty",
            prompt: "Veuillez saisir un mot de passe",
          },
          {
            type: "minLength[6]",
            prompt: "Votre mot de passe doit contenir au moins {ruleValue} caractères",
          },
        ],
      },
    },
  });

  $("#submitRegister").click(function (e) {
    // e.preventDefault();
    // Here we are checking is the form is not valid
    if (!$REGISTER_FORM.form("is valid")) {
      return;
    }

    /**
     * Here the form is valid so we don't submit because we will call the
     * register api with an ajax call
     */

    e.preventDefault();

    allFields = $REGISTER_FORM.form("get values");
    $.ajax({
      type: "POST",
      url: "/api/register",
      data: allFields,
      dataType: "json",
      success: function (response) {
        console.log(response);
        if (response.success) {
          const { data } = response;
          $(location).attr("href", '/profile');
        } else {
          $REGISTER_FORM.form("add errors", [response.message]);
        }
      },
    });
  });

  /** LOGIN FORM HANDLER */

  const $LOGIN_FORM = $("#loginForm");

  $LOGIN_FORM.form({
    fields: {
      username: {
        identifier: "username",
        rules: [
          {
            type: "empty",
            prompt: "Veuillez saisir un nom d'utilisateur",
          },
        ],
      },
      password: {
        identifier: "password",
        rules: [
          {
            type: "empty",
            prompt: "Veuillez renseigner un mot de passe",
          },
          {
            type: "minLength[6]",
            prompt: "Votre mot de passe doit faire 6 caractères ou plus ",
          },
        ],
      },
    },
  });

  $("#submitLogin").click(function (e) {
    // e.preventDefault();
    // Here we are checking is the form is not valid
    if (!$LOGIN_FORM.form("is valid")) {
      return;
    }

    /**
     * Here the form is valid so we don't submit because we will call the
     * login api with an ajax call
     */

    e.preventDefault();
    const allFields = $LOGIN_FORM.form("get values");

    $.ajax({
      type: "POST",
      url: "/api/login",
      data: allFields,
      dataType: "json",
      success: function (response) {
        console.log(response);

        if (response.success) {
          const { data } = response;
          $(location).attr("href", '/profile');
        } else {
          $LOGIN_FORM.form("add errors", [response.message]);
        }
      },
    });
  });

  /** RESERVATION FORM HANDLER */
  const $RESERVATION_FORM = $("#reservationForm");
  $RESERVATION_FORM.form({
    fields:{
      date:{
        identifier:"date",
        rules:[
          {
            type:"empty",
            prompt:"Veuillez sélectionner un créneau",
          },
        ],
      },
    },
  });



  $("#calendar").calendar({
    text: {
      days: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
          months: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Decembre'],
          monthsShort: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'],
          today: 'Aujourd\'hui',
          now: 'Maintenant',

    },
    ampm: false,
    minDate: new Date(today.getFullYear(), today.getMonth(), today.getDate()),
    maxDate: new Date(
        today.getFullYear() + 1,
        today.getMonth(),
        today.getDate(),
    ),
    formatter: {
      date: function (date) {
        if (!date) return "";
        var day = date.getDate();
        var month = date.getMonth() + 1;
        var year = date.getFullYear();
        return year + "-" + month + "-" + day;
      },
    },
  });

  $("#bookMe").click(function (e) {
    e.preventDefault();
    const allFields = $RESERVATION_FORM.form("get values");

    // let date = new Date(allFields["date"]);
    // date = date.toISOString().slice(0, 19).replace("T", " ");

    // allFields["date"] = date;

    $.ajax({
      type: "POST",
      url: "/api/booking",
      data: allFields,
      dataType: "json",
      success: function (response) {
        console.log(response);
        if (response.success) {
          $("#reservationForm").hide();
          $("#message-when-reserved").show();
        } else {
          $("#reservationForm").form("add errors",[response.message]);
        }
      },
    });
  });
});

function confirmationOfAppointment(formId,action){
  const $CONFIRMATION_FORM = $("#"+formId);

  if(action === "refuse"){
    allFields = $CONFIRMATION_FORM.form("get values");
    $.ajax({
      type: "POST",
      url: "/api/refuse",
      data: allFields,
      dataType: "json",
      success: function (response){
        console.log(response);
        if(response.success){
          $(location).attr('href','/profile/appointments');
        }},
    });

  }else{

    allFields = $CONFIRMATION_FORM.form("get values");
    $.ajax({
      type: "POST",
      url: "/api/confirm",
      data: allFields,
      dataType: "json",
      success: function (response){
        console.log(response);
        if(response.success){
          $(location).attr('href','/profile/appointments');
        }},
    });

  }
}

function adminDeletingAccount(formId,index){
  // const $ADMIN_DELETION_FORM = $("#"+formId);
  // allFields = $ADMIN_DELETION_FORM.form("get values");
  let id = document.getElementById(index+"-id").value;
  let role = document.getElementById(index+"-role").value;
  console.log(id + role);
  $.ajax({
    type:"POST",
    url:"/api/delete",
    data: {
      id:id,
      role:role,
    },
    dataType:"json",

    success:function (response){
      console.log(response);
      if(response.success){
        $(location).attr('href','profile/admin');
      }
    }
  })
}

