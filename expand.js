/* Describe the browser - set the appropriate one to true */
var isNav4, isNav6, isIE4;

/*
 * Set the browser identifier
 */
function setBrowser()
{
    if (navigator.appVersion.charAt(0) == "4") {
        if (navigator.appName.indexOf("Explorer") >= 0)
        {
            isIE4 = true;
        }
        else
        {
            isNav4 = true;
        }
    }
    else if (navigator.appVersion.charAt(0) > "4")
    {
        isNav6 = true;
    }
}

function getIdProperty( id, property )
{
  if (isNav6) {
    var styleObject = document.getElementById( id );
    if (styleObject != null) {
      styleObject = styleObject.style;
      if (styleObject[property]) {
        return styleObject[property];
      }
    }
    return( null );
  } else if (isNav4) {
    return document[id][property];
  } else {
    var ieobj = document.all.item(id)
    return ieobj.style[property];
  }
}

/*
 * Set the property of a specified id
 */
function setIdProperty( id, property, value)
{
  if (isNav6) {
    var styleObject = document.getElementById( id );
    if (styleObject != null) {
      styleObject = styleObject.style;
      styleObject[property] = value;
    }
  } else if (isNav4) {
      document[id][property] = value;
  } else if (isIE4) {
      var ieobj = document.all.item(id);
      ieobj.style[property] = value;
  }
}

/* Get the positions of the contracted element because we need to move
 * the expanded element to that position
 */
function moveElem(elemId, toElemId)
{
  var left = getIdProperty(toElemId, "left");
  var top = getIdProperty(toElemId, "top");

  if(!top) { top = 0; }

  if (isNav4) {
    leftPx = new Array( 0, left, "");
    topPx = new Array( 0, top, "");
  } else if (isNav6 || isIE4 ) {
    // Regex for stripping the 'px' from the top and left values
    var splitexp = /([-0-9.]+)(\w+)/;
    leftPx = splitexp.exec( left );
    topPx = splitexp.exec( top );
    if (leftPx == null || topPx == null) {
      leftPx = new Array(0, 0, "px");
      topPx = new Array(0, 0, "px");
    }
  }
  setIdProperty (elemId, "top", top + topPx[2]);
}

/*
 * Hide or display a page element usually a span or div.
 * elem: the element to operate on.
 * eorc: true to display the text; false to hide the text
 */
function expand_contract (elem, eorc)
{
  if(eorc) { // display the expand text and hide the expander hint 
    setIdProperty (elem + "_c", "display", "none");
    setIdProperty (elem + "_e", "display", "inline");
    moveElem (elem + "_e", elem + "_c");
  }
  else { // hide the text and display the expander hint
    setIdProperty (elem + "_c", "display", "inline");
    setIdProperty (elem + "_e", "display", "none");
  }
}
