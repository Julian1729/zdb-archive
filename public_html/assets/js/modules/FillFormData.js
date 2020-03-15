(function(){

  /**
   * Parse the visit info, and populate the form inputs
   * @return void
   */

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
        //console.log( document.querySelectorAll('[name="e16"]') );
        var input = document.querySelectorAll('[name="' + name + '"]');
        if(input.length >= 1){
          console.log('ran');
          // loop thorugh found elements
          for(var r=0;r<input.length;r++){
            var el = input[r];
            // check that is radio button
            if(el.getAttribute('type') === 'radio'){
              // which radio was selected?
              if(el.value === values[name]){
                // this was the selected button
                el.checked = true;
              }
            }else{
              // inject value
              el.value = values[name];
            }
          }
        }
      }
    }


    // handle field service activities
    var activities = JSON.parse(values.activities);
    for (var containerName in activities) {
      if (activities.hasOwnProperty(containerName)) {
        var a = activities[containerName];
        // get person by splitting container name
        var person = containerName.split('_')[2];
        // loop through array of info objects
        for(var i=0;i<a.length;i++){
          // create activiity element
          var aElement = Activity.createActivity(person);
          // inject info
          var aElement = Activity.injectActivityInfo(aElement, a[i]);
          // inject element into corresponding container
          Activity.injectActivity(containerName, aElement);
        }
      }
    }

}());
