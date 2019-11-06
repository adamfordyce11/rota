//Create Date Editor
var dateEditor = function(cell, onRendered, success, cancel){
    //cell - the cell component for the editable cell
    //onRendered - function to call when the editor has been rendered
    //success - function to call to pass the succesfully updated value to Tabulator
    //cancel - function to call to abort the edit and return to a normal cell

    //create and style editor
    var editor = $("<input type='date'></input>");
    editor.css({
        "padding":"3px",
        "width":"100%",
        "box-sizing":"border-box",
    });

    //Set value of editor to the current value of the cell
    editor.val(moment(cell.getValue(), "DD/MM/YYYY").format("YYYY-MM-DD"));

    //set focus on the select box when the editor is selected (timeout allows for editor to be added to DOM)
    onRendered(function(){
      editor.focus();
      editor.css("height","100%");
    });

    //when the value has been set, trigger the cell to update
    editor.on("change blur", function(e){
        success(moment(editor.val(), "YYYY-MM-DD").format("DD/MM/YYYY"));
    });

    //return the editor element
    return editor;
};

//create autocomplete editor
var autocompEditor = function(cell, onRendered, success, cancel){
    //create and style input
    var input = $("<input type='text'/>");

    //setup jquery autocomplete
    input.autocomplete({
        source: ["United Kingdom", "Germany", "France", "USA", "Canada", "Russia", "India", "China", "South Korea", "Japan"]
    });

    input.css({
        "padding":"4px",
        "width":"100%",
        "box-sizing":"border-box",
    })
    .val(cell.getValue());

    onRendered(function(){
        input.focus();
        input.css("height","100%");
    });

    //submit new value on blur
    input.on("change blur", function(e){
        if(input.val() != cell.getValue()){
            success(input.val());
        }else{
            cancel();
        }
    });

    //submit new value on enter
    input.on("keydown", function(e){
        if(e.keyCode == 13){
            success(input.val());
        }

        if(e.keyCode == 27){
            cancel();
        }
    });

    return input;
};

//Build Tabulator
$("#vessels").tabulator({
    height:"311px",
    columns:[
        {title:"Name", field:"name", width:150, editor:"input"},
        {title:"Location", field:"location", width:130, editor:autocompEditor},
        {title:"Progress", field:"progress", sorter:"number", align:"left", formatter:"progress", width:140, editor:true},
        {title:"Gender", field:"gender", editor:"select", editorParams:{"male":"Male", "female":"Female"}},
        {title:"Rating", field:"rating",  formatter:"star", align:"center", width:100, editor:true},
        {title:"Date Of Birth", field:"dob", align:"center", sorter:"date", editor:dateEditor},
        {title:"Driver", field:"car", align:"center", editor:true, formatter:"tickCross"},
    ],
});
