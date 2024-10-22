var isOpen = true;

function moveSideBarClose(){
    if (isOpen){
        document.querySelector("#sidebar").style.width = "0px";
        document.querySelector("#sidebarButton").style.marginLeft = "10px";
        document.querySelector("#ul").style.visibility = "hidden";
        isOpen = false;
    }
    else{
        document.querySelector("#sidebar").style.width = "200px";
        document.querySelector("#ul").style.visibility = "visible";
        document.querySelector("#sidebarButton").style.marginLeft = "155px";
        isOpen = true;
    }
}