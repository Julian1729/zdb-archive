/**
 * Activity Modules
 */

(function(){

  var _DOM = {
    brother_activity : document.getElementById('brother-activity').innerHTML,
    sister_activity : document.getElementById('sister-activity').innerHTML,
    activity_buttons : document.querySelectorAll('.add-activity-button'),
  }

  var counters = {
    bro : 0,
    sis : 0,
    uid : 0
  };

  function _renderActivity(){
    // "this" = button
    var dataGroup = this.getAttribute('data-group');
    if(!dataGroup){
      console.error('No "data-group" defined');
      return false;
    }
    var act = _createActivity( dataGroup );
    if(act){
      // throw onto html page
      this.parentNode.insertBefore(act, this);
    }else{
      console.error('Could not render activity');
    }
  }

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


  function _createActivity(dataGroup){
    // split data group to extract data (split by underscore "_")
    var split = dataGroup.split('_');
    // should be 'sis' or 'bro'
    var person = split[2];
    var template = null;
    // figure out who this activity is for, and choose corresponding activity
    if(person === 'sis'){
      template = _DOM.sister_activity;
    }else if (person === 'bro') {
      template = _DOM.brother_activity;
    }else{
      // data-group was not formatted correctly (daty-time-person)
      console.error('"' + person + '" is not a valid person for an activity button with data-group = "' + dataGroup + '"');
      return false;
    }
    // store the variables to be injected into template
    var templateVars = {
      data_group : dataGroup,
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
      name : dataGroup + '_' + counters[person]++
    };
    var htmlString = Mustache.to_html(template, templateVars);
    var actElement = _parseHTML(htmlString);
    // bind events to activity element
    var finalElement = _bindActivityUIActions(actElement);
    return finalElement;
  }

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

  /*
  Bind Events
   */
   // create activity buttons
   for(var i=0;i<_DOM.activity_buttons.length;i++){
     var btn = _DOM.activity_buttons[i];
     btn.onclick = _renderActivity;
   }

}());
