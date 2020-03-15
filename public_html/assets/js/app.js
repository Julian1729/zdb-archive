/**
 * Handle errors passed in by Formval
 * @param  HTMLElement form     [description]
 * @param  JSON response [description]
 * @return void
 */
function formvalErrorHandler(form, response){
  // users are informed on form the requirements for each inpus
  // error message are not needed
  var err;
  if(typeof response !== 'object'){
    // parse json
    err = JSON.parse(response);
  }else{
    err = response;
  }
  //get all inputs in form and remove failed class
  var allInputs = form.querySelectorAll('.fail')
  for(var i=0;i<allInputs.length;i++){
    allInputs[i].classList.remove('fail');
  }
  // loop through errors
  for (var name in err) {
    if (err.hasOwnProperty(name)) {
      // find input
      var input = form.querySelectorAll('[name=' + name + ']');
      // checkboxes need class added to parent
      for(var i=0;i<input.length;i++){
        var type = input[i].getAttribute('type');
        if( type === 'checkbox' || type === 'radio' ){
          input[i].parentElement.classList.add('fail');
        }else{
          input[i].classList.add('fail');
        }
      }
    }
  }

}
/**
 * Send an ajax request
 * @param  string URL to send request to
 * @param  string   method   HTTP Method to use, default POST
 * @param  string   params   parameters
 * @param  Function callback Function to call on success, passed response
 * @return void
 */
function ajaxRequest(url, method, params, callback){
  method = method || 'POST';
  // convert params to string if keyval obj
  if(typeof params === 'object'){
    params = encodeParams(params);
  }
  var x = new XMLHttpRequest();
  x.onreadystatechange = function(){
    if(this.readyState == 4 && this.status == 200){
      callback(this.responseText);
    }
  }
  x.open(method, url, true);
  x.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  x.send(params);
}

function encodeParams(params){
  var s = '';
  for (var p in params) {
    if (params.hasOwnProperty(p)) {
      s.length > 0 ? s += '&' : false;
      s += p + '=' + encodeURIComponent(params[p]);
    }
  }
  return s;
}

// Notice Module
(function(global){

  var Notice = function(level, message, id){
    return new Notice.init(level, message, id);
  }

  var noticeElement = null;

  Notice.prototype = {
    show : function(){
      this.noticeElement.classList.add('show');
    },
    hide : function(){
      this.noticeElement.classList.remove('show');
    }
  };

  Notice.init = function(level, message, id){
    // create container
    var container = document.createElement('DIV');
    // dafault level to nuetral
    level = level || 'neutral';
    // add classes to container
    container.className = 'notice ' + level;
    container.setAttribute('id', id);
    // create innerHTML
    var inner = document.createElement('P');
    inner.innerHTML = message;
    // append inner to container
    container.appendChild(inner);

    // add close button to notice and bind click event
    var closeButton = document.createElement('DIV');
    closeButton.className = 'notice-close ion-ios-close-empty'; // add ion-icon class
    //bind click evcent
    closeButton.onclick = this.hide.bind(this);
    container.appendChild(closeButton);

    // add element to instance
    this.noticeElement = container;
    // add to document
    document.body.insertBefore(container, document.body.firstChild);
    this.show();
  }

  Notice.init.prototype = Notice.prototype;

  global.Notice = Notice;

}(window));

// Drop Module
(function(global){

  var Dropper = function(message, level, id){
    return new Dropper.init(message, level, id);
  }

  /**
   * Active amount of droppers
   * @type {Number}
   */
  var active = 0;

  /**
   * Sum of height of all active droppers
   * @type {Number}
   */
  var height = 0;

  Dropper.prototype = {
    show : function(){

      if(height === 0){
        // there are no active Droppers, just move down 100%
        this.element.style.transform = 'translateY(100%)';
      }else{
        this.element.style.transform = 'translateY(' + (this.height + height) + 'px)';
      }
      // update height
      height += this.height;
    },
    hide : function(){
      this.element.style.transform = 'none';
      // subtract height from height sum
      height -= this.height;
    },
    remove : function(){
      this.element.remove();
    },
    disappear : function(delay){
      setTimeout(this.hide.bind(this), delay);
    }
  }

  Dropper.init = function(message, level, id){
    // create dropper element
      // create container
      var container = document.createElement('DIV');
      // add dropper class
      container.className = 'dropper ' + level;
      // add optional id
      if(id){
        container.setAttribute('id', id);
      }
      // create close button
      var closeBtn = document.createElement('DIV');
      closeBtn.className = 'ion-ios-close-empty close-button';
      // bind onlick to close button
      closeBtn.onclick = this.hide.bind(this);
      // add to container
      container.appendChild(closeBtn);
      // create message p element
      var text = document.createElement('P');
      text.textContent = message;
      // add to container
      container.appendChild(text);

      // attach dropper to document
      document.body.insertBefore(container, document.body.firstChild);

      // store element
      this.element = container;
      // store height
      this.height = container.offsetHeight;

      // show
      this.show();
      // disappear after 5 seconds
      this.disappear(5000);
  }

  Dropper.init.prototype = Dropper.prototype;

  global.Dropper = Dropper;

}(window));


if( document.getElementById('menu-parent') ){
  var menuButton = document.getElementById('menu-parent');
  menuButton.onclick = function(){
    document.getElementById('menu').classList.toggle('show');
  }
}

/**
 * Find the closest
 * @param  HTMLElement el  The HTML Element to use as the source
 * @param  string cls The class to find
 * @return HTMLElement The matching element
 */
function findAncestor(el, cls) {
    while ((el = el.parentElement) && !el.classList.contains(cls));
    return el;
}

/**
 * Scroll to the top of the window
 * @return void
 */
function scrollTop(){
  window.scrollTo(0,0);
}

function setDataText(textName, text){
  // find all elements with attribute
  var elements = document.querySelectorAll('[data-text=' + textName +']');
  // change text content for each element
  for(var e=0;e<elements.length;e++){
    elements[e].textContent = text;
  }
}

// INITIALIZE MODALS
var modalContainer = document.getElementById('modal-container');

function showModal(id){
  var modal = document.getElementById(id);
  if(!modal){
    console.error('Modal with id ' + id + ' not found');
    return;
  }
  if(!modal.classList.contains('modal')){
    console.log('Element not a modal');
    return;
  }
  // show container
  modalContainer.classList.add('show');
  // wait 2 sec, show modal
  modal.classList.add('show');
  return modal;
}

function closeModal(id){
  var modal = document.getElementById(id);
  if(!modal){
    console.error('Modal with id ' + id + ' not found');
    return;
  }
  if(!modal.classList.contains('modal')){
    console.log('Element not a modal');
    return;
  }
  // hide modal
  modal.classList.remove('show');
  // hide container
  modalContainer.classList.remove('show');
  return modal;
}

// close button
var modalCLoseBtn = document.getElementsByClassName('modal-close-button');
for(i=0;i<modalCLoseBtn.length;i++){
  modalCLoseBtn[i].onclick = function(){
    var container = findAncestor(this, 'modal');
    if(container){
      // hide modal
      container.classList.remove('show');
      // wait .3 seconds, then close container
      setTimeout(function(){
        modalContainer.classList.remove('show');
      }, 300)
    }
  }
}


var changeBtn = document.querySelectorAll('[data-modal]');
for(var i=0; i<changeBtn.length;i++){
  changeBtn[i].onclick = function(){
    // get for att
    var modalId = this.getAttribute('data-modal');
    var modal = showModal(modalId);
    modal.querySelector('input').focus();
  }
}
