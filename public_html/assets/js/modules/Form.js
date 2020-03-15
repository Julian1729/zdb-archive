/**
 * Initialize Forms with Formval, and add UI Actions
 */
(function(){
  var _cacheForms = {
    1 : {
      // Service Form
      element : document.getElementById('form1'),
      name : 'serviceform',
    },
    2 : {
      // Meal Form
      element : document.getElementById('form2'),
      name : 'mealform',
    },
    3 : {
      // Elder Form
      element : document.getElementById('form3'),
      name : 'elderform',
    }
  };

    var _formvalOptions = {
      url : URI.AJAX_URI,
      method : "POST"
    };

    /*
      ==========================================================
      BIND EVENTS
      ==========================================================
    */

        // Init Form with
        // for (var f in _cacheForms) {
        //   if (_cacheForms.hasOwnProperty(f)) {
        //     var el = F(_cacheForms[f].element, _formSubmitCallback, _formvalOptions);
        //     el.extras({
        //       action : 'visitForm',
        //       form : _cacheForms[f].name
        //     });
        //     el.submitButtonClick(_formButtonCallback);
        //   }
        // }

        for (var f in _cacheForms) {
          if (_cacheForms.hasOwnProperty(f)) {
            // inject formval handler into object
            _cacheForms[f].formval = (function(){
              var el = F(_cacheForms[f].element, _formSubmitCallback, _formvalOptions);
              el.extras({
                action : 'visitForm',
                form : _cacheForms[f].name
              });
              el.submitButtonClick(_formButtonCallback);
              return el;
            }());
          }
        }

  /*
    ==========================================================
    END BIND EVENTS
    ==========================================================
  */

    var _formUIActions = {
      left : function(form){
        form.classList.add('left');
        setTimeout(function(){
          form.classList.add('hide');
        },500);
      },
      center : function(form){
        form.classList.remove('hide');
        setTimeout(function(){
          form.classList.remove('left');
          form.classList.remove('right');
        }, 500);
      },
      right : function(form){
        form.classList.add('right');
        setTimeout(function(){
          form.classList.add('hide');
        },500);
      }
    }

    function _formButtonCallback(theForm){
      // FIXME: There is a way better way of doing this
      // check if the form is service form by id
      if(theForm.getAttribute('id') === 'form1'){
        // get activity info
        var activityInfo = Activity.getInfoObject();
        // inject into hidden textarea
        theForm.querySelector('#activity-data-holder').value = JSON.stringify(activityInfo);
      }

      // ==================================================================

      // OPTIMIZE: The order should be discerned using the _cacheForms obj
        // get order from "for" attribute
        var order = parseInt( theForm.getAttribute('data-order') );
      // find next form if there is one
      var nextForm = _cacheForms[(order + 1)];
      if(nextForm){
        // hide this form and show the next one
        _formUIActions.left(theForm);
        _formUIActions.center(nextForm.element)
      }
    }

    // OPTIMIZE: Implement Formval error handler into module
    function _formSubmitCallback(form, response){
      // is this the last form?
      var lastForm = false;
      if(parseInt(form.getAttribute('data-order')) === 3){
        lastForm = true;
      }
      var r = JSON.parse(response);
      switch (r.code) {
        case 1:
          if(lastForm){
            showModal('submit-success-modal');
          }
          //Notice('positive', 'Changes Saved');
        break;

        case 0:
          //Notice('negative', 'Please correct the errors in the previous form.');
          if(lastForm){
            Dropper('Please correct the errors in the form below', 'negative');
          }else{
            Dropper('Please correct the errors in the previous form', 'negative');
          }
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

    function _backButton(event){
      var order = parseInt( event.target.getAttribute('for') );
      var previousForm = _cacheForms[(order - 1)].element;
      _formUIActions.right( _cacheForms[order].element );
      _formUIActions.center( previousForm );
    }

    /*
      Bind Events
     */
     // Back Buttons
     var backButtons = document.querySelectorAll('button[data-action="back"]');
     for(var b=0;b<backButtons.length;b++){
       var btn = backButtons[b];
       btn.onclick = _backButton;
     }
     // Modal Button
     document.getElementById('modal-done-button').onclick = function(){
       window.location.href = URI.BASE_URI;
     }

}());
