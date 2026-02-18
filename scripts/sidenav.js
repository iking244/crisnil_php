function toggleNav() {
    var sidenav = document.getElementById("mySidenav");
    var mainContent = document.getElementById("main");
    
    if (sidenav.style.width === "270px") {
        sidenav.style.width = "0"; 
        mainContent.style.marginLeft = "0"; 
    } else {
        sidenav.style.width = "270px"; 
        mainContent.style.marginLeft = "270px"; 
    }
}
