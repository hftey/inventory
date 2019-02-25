<!--

    var iTreeCount = 1;
    
    d = new dTree('afcTree');
    
    /* Reset the tree obect and display */
    function resetTree($layerID){
        d = new dTree('d');
        iTreeCount = 1;
        document.getElementById($layerID).innerHTML = '';
    }
    /* Display Treeview */
    function showTree($layerID){
        document.getElementById($layerID).innerHTML = d;
    }
    /* Load some sample tree items */
    function loadTreeItems(){
        for(i=0; i<3; i++){
            tmpLink = '#link_'+iTreeCount;
            tmpName = 'Item_'+iTreeCount;
            addTreeItems(0, tmpName, tmpLink, '', '', '', '', '');
            
            for(j=0; j<3; j++){
                tmpSubLink = '#sublink_'+iTreeCount;
                tmpSubName = 'subItem_'+iTreeCount;
                addTreeItems(iTreeCount-1, tmpSubName, tmpSubLink, '', '', '', '', '');
            }
        }
    }
    /* Add tree items into the tree view */
    function addTreeItems(itemParent, itemName, itemLink, itemTitle, itemTarget, itemIcon, itemIconOpen, itemOpen){
       if(itemParent == ''){ itemParent = 0; }
        if(itemLink == ''){ itemLink = '#'; }
        if(itemOpen == ''){ itemOpen = false; }
        if(itemTarget == ''){ itemTarget = '_self'; }
        if(itemName != ''){
            d.add(iTreeCount, itemParent, itemName, itemLink, itemTitle, itemTarget, itemIcon, itemIconOpen, itemOpen);
            iTreeCount++;
            return true;
        } else { return false; }
    }
    
//-->