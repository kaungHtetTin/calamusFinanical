export function setVisibility(view,visible){
    if(visible){
        view.setAttribute('style','');
    }else{
        view.setAttribute('style','display:none');
    }
}

export function setText(view,text){
    view.innerHTML=text;
}

export function findById(id){
    return document.getElementById(id);
}

export function addItem(view,layout){
    view.innerHTML+=layout;
}

export function clearItem(view){
    view.innerHTML="";
}

 