// Get date values
window.onload = function(){
    var currdate = new Date();
  
    var curr_year = currdate.getFullYear();
    var curr_month = ("0" + (currdate.getMonth() + 1)).slice(-2);
    var curr_day = ("0" + currdate.getDate()).slice(-2);
  
    var dateFormat = curr_year + '-' + curr_month + '-' + curr_day;
  
    document.getElementById("manufacturingDate").setAttribute("max", dateFormat);
    document.getElementById("expiryDate").setAttribute("min", dateFormat);
    document.getElementById("dateOfArrival").setAttribute("max", dateFormat);
  }
  