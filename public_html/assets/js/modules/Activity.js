/**
 * Activity Module
 */

var Activity = (function(){


  /**
   * Cache needed DOM elements and strings
   * @type {Object}
   */
  var _DOM = {
    brother_activity : document.getElementById('brother-activity').innerHTML,
    sister_activity : document.getElementById('sister-activity').innerHTML,
    activity_buttons : document.querySelectorAll('.add-activity-button'),
    activity_containers : (function(){
      // collect containers and organize by data group
      // store containers here, to be returned { data_group : element}
      var containers = {};
      var collection = document.querySelectorAll('.activity-container');
      // loop through collection of containers and store in object
      for(var i=0;i<collection.length;i++){
        var c = collection[i];
        var dataGroup = c.getAttribute('data-group');
        if(!dataGroup){
          throw new Error('No data group defined for activity container');
        }
        // enter into object
        containers[dataGroup] = c;
      }
      return containers;
    }())
  };

  var activity_value_map = {
    1 : 'sheperding_call',
    2 : 'bible_study',
    3 : 'return_visit'
  }


  /*
    ==========================================================
    BIND EVENTS
    ==========================================================
  */

    // Activity Buttons
      for(var b=0;b<_DOM.activity_buttons.length;b++){
        var btn = _DOM.activity_buttons[b];
        btn.onclick = _addActivity;
      }

  /*
    ==========================================================
    END BIND EVENTS
    ==========================================================
  */

  /**
   * Hold and update the count of each activity.
   * This is used to give unique names to each radiogroup and
   * unique ids to each radio input and its corresponding label
   * @type {Object}
   */
  var counters = {
    bro : 0,
    sis : 0,
    uid : 0
  };

  /**
   * Convert an HTML string to a DOM element
   * @param       {[string]} html HTML string
   * @return      {[DOMElement]} DOM Element
   */
  function _parseHTML(html){
    try {
      var parser = new DOMParser();
      doc = parser.parseFromString(html, "text/html");
      return doc.body.firstChild;
    } catch (e) {
      var tempWrapper = document.createElement('DIV');
      tempWrapper.innerHTML = html;
      return tempWrapper.firstElementChild;
    }
  }


  /**
   * Bind UI Events to Activity elements radiogroups and forms
   * @param       {[HTMLElement]} activity The Activity
   * @return      {[HTMLElement]} Activity with UI Events Binded
   */
  function _bindActivityUIActions(activity){
    // Elements
    var publisherForm = activity.querySelector('.publisher-form');
    var shepherdingForm = activity.querySelector('.shepherding-form');

      function showShepForm(){
        publisherForm.classList.add('hide');
        shepherdingForm.classList.remove('hide');
      }
      function showPubForm(){
        if(shepherdingForm){
          shepherdingForm.classList.add('hide');
        }
        publisherForm.classList.remove('hide');
      }
      function deleteRow(){
        activity.remove();
      }

    // Bind Events
      // Shepherding Call Button
      var shepBtn = activity.querySelector('input[value="1"]');
      if(shepBtn){
        shepBtn.onclick = function(){
          if(this.checked){
            showShepForm();
          }
        };
      }
      // Bible Study Button
      activity.querySelector('input[value="2"]').onclick = function(){
        if(this.checked){
          showPubForm();
        }
      };
      // Return Visit Button
      activity.querySelector('input[value="3"]').onclick = function(){
        if(this.checked){
          showPubForm();
        }
      };
      // Delete Button
      activity.querySelector('.delete-row').onclick = deleteRow;

    return activity;
  }

  /**
   * Create activity element
   * @param       {[string]} person "bro" || "sis"
   * @return      {[DOMElement]} Activity Element
   */
  function createActivity(person){
    var template = null;
    // get template based on who person is
    if(person === 'bro'){
      template = _DOM.brother_activity;
    }else if (person === 'sis') {
      template = _DOM.sister_activity;
    }else{
      throw new Error('Invalid person param');
    }
    // store the variables to be injected into template
    var templateVars = {
      // hold the ids of the radio buttons, to be inputed into the labels
      counter : [],
      rid : function(){
        var n = 'r' + counters.uid;
        this.counter.push(n);
        counters.uid++;
        return n;
      },
      lid : function(){
        // grab first item, which would be the id of the corresponding input, and delete it
        return this.counter.shift();
      },
      name : person + '_radiogroup_' + counters[person]++
    };
    // html string with rendered html
    var renderedHTML = Mustache.to_html(template, templateVars);
    // convert html string to element
    var actElement = _parseHTML(renderedHTML);
    // bind ui actions to element
    var finishedElement = _bindActivityUIActions(actElement);
    return finishedElement
  }

  /**
   * To be called when Add Activity Button clicked
   * @param       {[Object | String]} e Event or data group string
   */
  function _addActivity(e){
    // discern whether e is an object, in which case we must
    // extract the data-group from the event or
    // the data-group was passed in as a string
    var dataGroup = null;
    if(typeof e === 'object'){
        try{
          dataGroup = e.target.getAttribute('data-group')
        }catch (e){
          throw new Error('No data group defined for target')
        }
    }else if (typeof e === 'string') {
      dataGroup = e;
    }else{
      throw new Error('Invalid data-group');
    }
    // discern who activity is for, split data group to extract data (split by underscore "_")
    var split = dataGroup.split('_');
    // should be 'sis' or 'bro'
    var person = split[2];
    // create activity
    var activity = createActivity(person);
    // inject activity into corresponding container
    injectActivity(dataGroup, activity);
  }

  /**
   * Insert Activity Element into activity container div
   * @param  {[string]} containerName  data-group of container
   * @param  {[HTMLElement]} activityElement Created activity to inserted
   * @return void
   */
  function injectActivity(containerName, activityElement){
    // find container
    var container = _DOM.activity_containers[containerName];
    if(!container){
      throw new Error('No container found for data-group ' + containerName);
    }
    // append to container
    container.appendChild(activityElement, container);
  }

  function injectActivityInfo(activity, data){
    // get activity type
    var type = data.type;
    // numeric value of type
    var value = null;
    // get numeric value of type
    for (var v in activity_value_map) {
      if (activity_value_map.hasOwnProperty(v)) {
        if(activity_value_map[v] === type){
            value = v;
            break;
        }
      }
    }
    // check that a value was found
    if(!value){
      throw new Error('No value found for activity type');
    }
    // get radio input with value and set to checked
    var selected = activity.querySelector('input[value="' + value + '"]');
      // set to checked
      selected.checked = true;
    // set input values and display correct form
    if(type === 'sheperding_call'){
      // set elder, publisher, and situation values
      activity.querySelector('input[name="shep_elder"]').value = data.elder;
      activity.querySelector('input[name="shep_publisher"]').value = data.publisher;
      activity.querySelector('textarea[name="shep_situation"]').value = data.situation;
      // remove hide from sheperding form
      activity.querySelector('.shepherding-form').classList.remove('hide');
    }else if (type === 'bible_study' || type === 'return_visit') {
      // set publisher name
      activity.querySelector('input[name="pub_name"]').value = data.publisher;
      // remove hide from publisher form
      activity.querySelector('.publisher-form').classList.remove('hide');
    }
    // return modified activity
    return activity;
  }

  function collectActivityInfo(){
    var infoObject = {};
    // loop through activity containers
    for (var a in _DOM.activity_containers) {
      var containerData = [];
      if (_DOM.activity_containers.hasOwnProperty(a)) {
        var container = _DOM.activity_containers[a];
        // find activities inside container
        var activities = container.querySelectorAll('.activity');
        //loop through activities inside container
        for(var i=0;i<activities.length;i++){
          var activity = activities[i];
          var info = _extractActivityInfo(activity, true);
          //console.log(info);
          if(info){
          containerData.push(info);
          }
        }
        // add info to infoObject { data_group : [{objects}]}
        infoObject[a] = containerData;
      }
    }
    return infoObject;
  }

  function _extractActivityInfo(activity, validate){
    validate = validate || false;
    // store data here
    var data = {};
    // get value of radiogroup by looping through radio inputs
    // and selecting the checked one
    var selection = activity.querySelector('.radiogroup input[type="radio"]:checked');
        // var selectedValue = null;
        // var radiobuttons = activity.querySelectorAll('.radiogroup input[type="radio"]');
        // for(var i=0;i<radiobuttons.length;i++){
        //   var btn = radiobuttons[i];
        //   if(btn.checked){
        //     selectedValue = btn.value;
        //     break;
        //   }
        // }
    // check that a value was selected
    if(!selection){
      // do we need to validate?
      if(validate){
        // we must add failed class to radio button containers
        var buttonContainers = activity.querySelectorAll('.radiobutton-container');
        for(var i=0;i<buttonContainers;i++){
          buttonContainers[i].classList.add('fail');
        }
      }else{
        console.error('No selection inside radiogroup');
      }
      return null;
    }
    // store type string by matching value with object map
    data.type = activity_value_map[selection.value];
    if(!data.type){
      return null;
    }
    // grab appropriate values based on activity type
    if (data.type === 'sheperding_call') {
        // add shep elder, publisher, and situation values
        var inputs = {
          elder : activity.querySelector('input[name="shep_elder"]'),
          publisher : activity.querySelector('input[name="shep_publisher"]'),
          situation : activity.querySelector('textarea[name="shep_situation"]')
        }
        for (var i in inputs) {
          if (inputs.hasOwnProperty(i)) {
            var el = inputs[i];
            if(el.value){
              // enter into data element
              data[i] = el.value;
            }else if (validate === true) {
              // add fail to input
              el.classList.add('fail');
            }
          }
        }

      }else if(data.type === 'bible_study' || data.type === 'return_visit'){
        // add publisher input value
        var input = activity.querySelector('input[name="pub_name"]');
        if(input.value){
          data.publisher = input.value;
        }else if (validate === true) {
          input.classList.add('fail');
        }
      }
    // return info
    return data;
  }



  return {
    createActivity : createActivity,
    injectActivity : injectActivity,
    injectActivityInfo : injectActivityInfo,
    getInfoObject : collectActivityInfo
  }

}());
