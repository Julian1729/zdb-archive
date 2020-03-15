/**
 * Forms.js
 *
 * Javascript for congregation visit forms
 */

/**
 * Parse the visit info, and populate the form inputs
 * @return void
 */
function fillData(){
  // loop through visit info
  var values = {};
  for (var column in visitObj) {
    if (visitObj.hasOwnProperty(column)) {
      if(visitObj[column] !== ''){ // check that the value is not empty
        // find corresponding name
        var name = columnMap[column];
        values[name] = visitObj[column];
      }
    }
  }
  // loop through data and set data values
  for (var name in values) {
    if (values.hasOwnProperty(name)) {
      // find corresponding input
      var input = document.querySelector('[name="' + name + '"]');
      // if input exists, set value
      if(input){
        input.value = values[name];
      }
    }
  }
  // handle field service activities
}

fillData();


/*
Initialize Activity Div
 */
  var broActCount = 0;
  var sisActCount = 0;
  var id = 0;

  /**
   * Create activity element and bind click events
   * @param  {string} prefix Prefix of name based on create activity button
   * @return {DOMElement} Activity element
   */
  function createActivity(prefix){
    // extract info form prefix
    var split = prefix.split('-');
    var person = split[2];
    // copy html from template of corresponding form
    var activityHTML = "";
    var sis = false; // used as a easy way to tell if this is for sister
    if(person === 'sis'){
      activityHTML = document.getElementById('sister-activty').innerHTML;
      sis = true;
      // increment activity counter
      sisActCount++;
    }else{
      activityHTML = document.getElementById('brother-activity').innerHTML;
      // increment activity counter
      broActCount++;
    }

    // convert activityHTML into DOM Element
      var tempWrapper = document.createElement('DIV');
      tempWrapper.innerHTML = activityHTML;
      // get first child (activity)
    var activityElement = tempWrapper.firstElementChild;
    // set data-group to activity
    activityElement.setAttribute('data-group', prefix);
      // inner elements
      var pubForm = activityElement.querySelector('.publisher-form');
      var shepForm = activityElement.querySelector('.sheperding-form');

    // loop through radio buttons give name and unique ids to each radio button, and their corresponding labels
      var radioButtons = activityElement.querySelectorAll('.radiobutton-container');
      var name = prefix + '_' + (sis ? sisActCount : broActCount);
      var id = 'r' + (sis ? sisActCount : broActCount);
      for(r=0;r<radioButtons.length;r++){
        var container = radioButtons[r];
        // get actual radiobutton input
        var btn = container.querySelector('input[type=radio]');
          // assign name
          btn.setAttribute('name', name);
          // assign id
          btn.setAttribute('id', id);
        // get label
        var label = container.querySelector('label');
          // assign for
          label.setAttribute('for', id);
      }

    function showPubForm(){
      if(shepForm){
        shepForm.classList.add('hide');
      }
      pubForm.classList.remove('hide');
    }

    function showShepForm(){
      pubForm.classList.add('hide');
      shepForm.classList.remove('hide');
    }

    function hideAllForms(){
      pubForm.classList.add('hide');
      if(shepForm){
        shepForm.classList.add('hide');
      }
    }

  // BIND EVENTS TO ELEMENTS
  // shep button
  if(!sis){
    var shep_radio = activityElement.querySelector('input[value="1"]');
    shep_radio.onclick = function(){
      if(this.checked){
        showShepForm();
      }
    }
  }
  // rv button
  activityElement.querySelector('input[value="2"]').onclick = function(){
    if(this.checked){
      showPubForm();
    }
  }
  // bible study button
  activityElement.querySelector('input[value="3"]').onclick = function(){
    if(this.checked){
      showPubForm();
    }
  }

  // delete button
  activityElement.querySelector('span.delete-row').onclick = function(){
      activityElement.remove();
  }

  return activityElement;

}

// INIT ACTIVITY BUTTONS
  // get all buttons
  var addActivityButtons = document.querySelectorAll('.add-activity-button');
  // bind on click handler to each button
  for(var a=0;a<addActivityButtons.length;a++){
    addActivityButtons[a].onclick = function(){
      // get day
      var prefix = this.getAttribute('data-prefix');
      if(!prefix){
        console.error('No day specified for');
      }
      var activity = createActivity(prefix);
      this.parentNode.insertBefore(activity, this);
    }
  }


/*
Handle sheperding visit checkboxes
 */
// DEPRECATED
// var shepchecks = document.getElementsByClassName('shepcheck');
// for(var i=0;i<shepchecks.length;i++){
//   shepchecks[i].onclick = function(){
//     var id = this.getAttribute('for');
//     if(this.checked){
//       // show element with id
//       document.getElementById(id).classList.remove('hide');
//     }else{
//       document.getElementById(id).classList.add('hide');
//     }
//   }
// }

/*
Handle radio buttons
 */
// var radioButtons = document.querySelectorAll('.radiogroup input[type=radio]');
// for(i=0;i<radioButtons.length;i++){
//   radioButtons[i].onclick = function(){
//     if(this.checked){
//       var suffix = findAncestor(this, 'radiogroup').getAttribute('data-group');
//       var value = this.getAttribute('value');
//       // sheperding form
//       var shep = document.getElementById('shep' + suffix);
//       var pub = document.getElementById('pub' + suffix);
//       // publisher form
//       switch (value) {
//         case '0':
//           //none, hide all inputs
//           (shep) ? shep.classList.add('hide') : false;
//           pub.classList.add('hide');
//           break;
//         case '1':
//           // sheperding call
//           pub.classList.add('hide');
//           shep.classList.remove('hide');
//           break;
//         case '2':
//           // bible study
//           (shep) ? shep.classList.add('hide') : false;
//           pub.classList.remove('hide');
//           break;
//         case '3':
//           // return visit
//           (shep) ? shep.classList.add('hide') : false;
//           pub.classList.remove('hide');
//           break;
//         default:
//           (shep) ? shep.classList.add('hide') : false;
//           pub.classList.add('hide');
//           break;
//       }
//     }
//   }
// }

/*
Initialize form back buttons
 */
var backButtons = document.querySelectorAll('button[data-action=back]');
for(var i=0;i<backButtons.length;i++){
  var button = backButtons[i];
  button.onclick = function(){
    var form = parseInt(this.getAttribute('for'));
    // add 1 to current form to get next form id
    var nextFormId = 'form' + (form + 1).toString();
    // subtract 1 from current form to get previous form id
    var prevFormId = 'form' + (form - 1).toString();

    // find next form element
    var nextForm = (el = document.getElementById( nextFormId ) ) ? el : false;
    var prevForm = (el = document.getElementById( prevFormId ) ) ? el : false;
    var currentForm = document.getElementById( 'form' + form );


    function toLeft(form){
      form.classList.add('left');
      setTimeout(function(){
        form.classList.add('hide');
      },500);
    }
    function toRight(form){
      form.classList.add('right');
      setTimeout(function(){
        form.classList.add('hide');
      },500);
    }
    function toCenter(form){
      form.classList.remove('hide');
      setTimeout(function(){
        form.classList.remove('left');
        form.classList.remove('right');
      }, 500);
    }

    if(prevForm){
      toCenter(prevForm);
      toRight(currentForm);
    }
  }
}

/*
Intitialize form activity buttons
 */

// INIT FORMS WITH FORMVAL
function formCallback(form, response){
  console.log(response);
  var r = JSON.parse(response);
  switch (r.code) {
    case 1:
      //Notice('positive', 'Changes Saved');
      Dropper('Changes Saved', 'positive');
    break;

    case 0:
      //Notice('negative', 'Please correct the errors in the previous form.');
      Dropper('Please correct the errors in the previous form', 'negative');
      formvalErrorHandler(form, r.errors);
    break;

    case 3:
      //Notice('neutral', 'No changes saved');
      Dropper('No changes saved', 'neutral');
      if(r.errors){
        Dropper('Please correct the errors in the previous form', 'negative');
        formvalErrorHandler(form, r.errors);
      }
    break;
  }
}

// FINAL FORM (SUBMIT) callback
function submissionCallback(form, response){
  var r = JSON.parse(response);
  switch (r.code) {
    case 1:
      //Notice('positive', 'Changes Saved');
      showModal('submit-success-modal');
    break;

    case 0:
      //Notice('negative', 'Please correct the errors in the previous form.');
      Dropper('Please correct the errors in the form below', 'negative');
      formvalErrorHandler(form, r.errors);
    break;

    case 3:
      //Notice('neutral', 'No changes saved');
      Dropper('No changes saved', 'neutral');
      if(r.errors){
        Dropper('Please correct the errors in the last form', 'negative');
        formvalErrorHandler(form, r.errors);
      }
    break;
  }
}

/*
Initialize Modal Done Button
 */
document.getElementById('modal-done-button').onclick = function(){
  // send home
  window.location.href = URI.HOME_URI;
}


/**
 * Called when submit button is clicked
 * @param  HTMLElement currentForm The form that is being submitted
 * @return void
 */
function submitButtonCallback(currentForm){

  function toLeft(form){
    form.classList.add('left');
    setTimeout(function(){
      form.classList.add('hide');
    },500);
  }
  function toRight(form){
    form.classList.add('right');
    setTimeout(function(){
      form.classList.add('hide');
    },500);
  }
  function toCenter(form){
    form.classList.remove('hide');
    setTimeout(function(){
      form.classList.remove('left');
      form.classList.remove('right');
    }, 500);
  }

  // get corresponding form
  var form = parseInt(this.getAttribute('for'));
  // add 1 to current form to get next form id
  var nextFormId = 'form' + (form + 1).toString();
  // subtract 1 from current form to get previous form id
  var prevFormId = 'form' + (form - 1).toString();
  // prefix for to et current form id

  // find next form element
  var nextForm = (el = document.getElementById( nextFormId ) ) ? el : false;
  var prevForm = (el = document.getElementById( prevFormId ) ) ? el : false;
  // get action
  switch (this.getAttribute('data-action')) {
    case 'next':
      if(nextForm){
        toLeft(currentForm);
        toCenter(nextForm);
        scrollTop();
      }
      break;
    case 'submit':
      console.log('submit');
      break;
    default:
      console.log('default');
  }
}

// global variables ... store activities here to be passed in with extras
var wed_acts_bro, thurs_acts_bro, fri_acts_bro;
var wed_acts_sis, thurs_acts_sis, fri_acts_sis;
function serviceFormCallback(currentForm){
  // handle the actual form switch
  submitButtonCallback.call(this, currentForm);
  // handle the activities
  var activities = getActivities();
  wed_acts_bro = JSON.stringify(activities.bro.wed);
  thurs_acts_bro = JSON.stringify(activities.bro.thurs);
  fri_acts_bro = JSON.stringify(activities.bro.fri);

  wed_acts_sis = JSON.stringify(activities.sis.wed);
  thurs_acts_sis = JSON.stringify(activities.sis.thurs);
  fri_acts_sis = JSON.stringify(activities.sis.fri);

  serviceForm.extras({
    action : 'visitForm',
    form : 'serviceform',
    // pass in activities as extras
    'wed_bro' : wed_acts_bro,
    'thurs_bro' : thurs_acts_bro,
    'fri_bro' : fri_acts_bro,
    'wed_sis' : wed_acts_sis,
    'thurs_sis' : thurs_acts_sis,
    'fri_sis' : fri_acts_sis
  });

}

/**
 * Get all activities from the form and gather there info
 * @return object Bro and Sis info
 */
function getActivities(){

  function getInfo(activities){
    if(!activities){
      return {};
    }
    // store data here
    var data = {};
    // loop through activities
    for(var a=0;a<activities.length;a++){
      var act = activities[a];
      var prefix = act.getAttribute('data-group');
      // get value of radiogroup, by looping through radio buttons and selecting the checked one
      var selection = act.querySelector('.radiogroup input[name^="' + prefix + '"]:checked');

      // make sure that the user selected an activity
      var rBtns = act.querySelectorAll('.radiobutton-container'); // all radio containers
      if(!selection){
        // user did not select an activity, add failed class to all radio container
        for(var r=0;r<rBtns.length;r++){
          rBtns[r].classList.add('fail');
        }
        // skip iteration
        continue;
      }else{
        // remove failed class from any radio buttons
        for(var r=0;r<rBtns.length;r++){
          rBtns[r].classList.remove('fail');
        }
      }
      // selected value 1 = shep call, 2 = bible study, 3 = return visit
      var value = selection.value;
      switch (value) {
        case "1":
          // grab sheperding call info
          // FIXME: Validate Inputs here and for the the rest
          var elder = act.querySelector('input[name^="' + prefix + '_shep_elder"]').value;
          var pub = act.querySelector('input[name^="' + prefix + '_shep_pub"]').value;
          var sit = act.querySelector('textarea[name^="' + prefix + '_shep_sit"]').value;
          data['shep'] = {
            'elder' : elder,
            'pub' : pub,
            'sit' : sit
          };
          break;
        case "2":
          var pub = act.querySelector('input[name^="' + prefix + '_pub"]').value;
          data['bs'] = {
            'pub' : pub
          };
          break;
        case "3":
          var pub = act.querySelector('input[name^="' + prefix + '_pub"]').value;
          data['rv'] = {
            'pub' : pub
          };
          break;
      }
    }
    return data;
  }
  // get all activities for each day
    // get bro activities
      // wednesday
      var wed_bro_act = document.querySelectorAll('[data-group="wed_bro_act"]');
      var wed_bro_info = getInfo(wed_bro_act);
      // thursday
      var thurs_bro_act = document.querySelectorAll('[data-group="thurs_bro_act"]');
      var thurs_bro_info = getInfo(thurs_bro_act);
      // friday
      var fri_bro_act = document.querySelectorAll('[data-group="fri_bro_act"]');
      var fri_bro_info = getInfo(fri_bro_act);
    // get sis activities
      // wednesday
      var wed_sis_act = document.querySelectorAll('[data-group="wed_sis_act"]');
      var wed_sis_info = getInfo(wed_sis_act);
      // thursday
      var thurs_sis_act = document.querySelectorAll('[data-group="thurs_sis_act"]');
      var thurs_sis_info = getInfo(thurs_sis_act);
      // friday
      var fri_sis_act = document.querySelectorAll('[data-group="fri_sis_act"]');
      var fri_sis_info = getInfo(fri_sis_act);

  // return info
  return {
    bro : {
      'wed' : wed_bro_info,
      'thurs' : thurs_bro_info,
      'fri' : fri_bro_info
    },
    sis : {
      'wed' : wed_sis_info,
      'thurs' : thurs_sis_info,
      'fri' : fri_sis_info
    }
  };

}

// Service Form Formval
var serviceForm = F('form1', formCallback,{
  url : URI.AJAX_URI,
  method : "POST",
});
serviceForm.submitButtonClick(serviceFormCallback);

// Meal Form Formval
var mealSchedule = F('form2', formCallback,{
  url : URI.AJAX_URI,
  method : "POST",
});
mealSchedule.extras({
  action : 'visitForm',
  form : 'mealform'
});
mealSchedule.submitButtonClick(submitButtonCallback);

// Elder Form Formval
var elderForm = F('form3', submissionCallback,{
  url : URI.AJAX_URI,
  method : "POST",
});
elderForm.extras({
  action : 'visitForm',
  form : 'elderform'
});
elderForm.submitButtonClick(submitButtonCallback);
