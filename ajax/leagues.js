//
// Function to take the selected items in the available[] list and add them
// to the teamsinlge[] select list
//
function add_to_league()
{
  var i;

  var avail = document.getElementById('available');
  var inlge = document.getElementById('teamsinlge');
  var numinlge = inlge.options.length;
  for(i=0; i<avail.options.length; ++i)
  {
    if(avail.options[i].selected == true)
    {
      var tolge = new Option(avail.options[i].text, avail.options[i].value);
      inlge[numinlge] = tolge;
      inlge.options[numinlge].selected = true;
      ++numinlge;
      avail.remove(i);
    }
  }
  enable_button(document.leaguemebers.remove);
  if(!avail.options.length) {disable_button(document.leaguemebers.add);}
}

//
// Function to take the selected items in the teamsinlge[] list and add them
// to the available[] select list
//
function remove_from_league()
{
  var i;

  var avail = document.getElementById('available');
  var inlge = document.getElementById('teamsinlge');
  var numavail = avail.options.length;
  for(i=0; i<inlge.options.length; ++i)
  {
    if(inlge.options[i].selected == true)
    {
      var toavail = new Option(inlge.options[i].text, inlge.options[i].value);
      avail[numavail] = toavail;
      avail.options[numavail].selected = true;
      ++numavail;
      inlge.remove(i);
    }
  }
  enable_button(document.leaguemebers.add);
  if(!inlge.options.length) {disable_button(document.leaguemebers.remove);}
}

// Function to select all the teams that are for the league so that the id's
// are captured when the form is submitted
function select_all_teams()
{
  var i;

  var inlge = document.getElementById('teamsinlge');
  for(i=0; i<inlge.options.length; ++i)
  {
    inlge.options[i].selected = true;
  }
  return(true);
}

// Check whether league teams select boxes are empty and disable the add or
// remove button accordingly
// The form name containing the buttons is 'leaguemebers' and the 'add teams'
// button is 'add' and the remove button is 'remove'.
function set_button_state()
{
  var avail = document.getElementById('available');
  var numavail = avail.options.length;
  var inlge = document.getElementById('teamsinlge');
  var numinlge = inlge.options.length;

  if(!numinlge) { disable_button(document.leaguemebers.remove); }
  if(!numavail) { disable_button(document.leaguemebers.add); }
}

function disable_button(button)
{
   var butname = button.name;
   if (document.all || document.getElementById)
     button.disabled = true;
   else if (button) {
     button.oldOnClick = button.onclick;
     button.onclick = null;
     button.oldValue = button.value;
     button.value = 'DISABLED';
   }
}
function enable_button (button) {
   if (document.all || document.getElementById)
     button.disabled = false;
   else if (button) {
     button.onclick = button.oldOnClick;
     button.value = button.oldValue;
   }
}
