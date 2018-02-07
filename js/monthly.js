(function($){
  $.fn.hasAttr = function(name) {  
    return this.attr(name) !== undefined;
  };

  $.fn.extend({
    monthly:function(options){
      var defaults = {
        id: this.selector,
        summary: "",
        summaryKey: "",
        period: "today",
        action: "monthly",
        url: "controller.php",
        force: "rw",
        user: "single",
      };
      var options = $.extend(defaults,options);
      var totals=Array();
      var totalDays=Array();
      var ws,wsState=false;

      function newMonth(p) {
        $(options.id+" #monthly").remove()                           // Remove if it already exists
        $("#monthlyNewPeriod").remove();
        document.title = "Create a new monthly period";              // Set the document title
        $.get(options.url, { 'request':"getLastEntry" }, null,"json").done(function(data){
          var first=data+(60*60*24);
          var t=Array(), tp=-1;               // Declare an array and an index to build the HTML
          t[++tp] = "<div id='monthlyNewPeriod'>";
          t[++tp] = "  <table id='monthly'>";
          t[++tp] = "    <thead id='monthlyHeader'><tr>";
          t[++tp] = "      <th colspan='32'>New Period";
          t[++tp] = "        <div class='titlebtn' id='monthlyPreviousButton'><img src='img/before.png' /></div>";
          t[++tp] = "      </th>";
          t[++tp] = "    </tr></thead>";
          t[++tp] = "  </table>";
          t[++tp] = "  <form id='monthlyFormLeft'>";
          t[++tp] = "    <p>";
          d=new Date(first*1000);
          t[++tp] = "      <input class='textbox' type='text' id='monthlyNewPeriodFirst' time='"+first+"' value='Start date: "+d.getDate()+"/"+Number(d.getMonth()+1)+"/"+d.getFullYear()+"'></input>";
          t[++tp] = "    </p>";
          t[++tp] = "    <p>";
          t[++tp] = "      <label class='label'>For how many days </label>";
          t[++tp] = "      <input class='textbox' type='text' id='monthlyNewPeriodLength' value='31'></input>";
          t[++tp] = "    </p>";
          t[++tp] = "    <p>";
          t[++tp] = "      <select class='field' id='monthlyResourceSelect'></select></li>";
          t[++tp] = "    </p>";
          t[++tp] = "  </form>";
          t[++tp] = "  <div id='monthlyFormRight'>";
          t[++tp] = "    <div id='createPeriod'>Create</div>";
          t[++tp] = "  </div>";
          t[++tp] = "  <table id='monthlyFormCenter'>";
          t[++tp] = "    <thead>";
          t[++tp] = "      <th class='right'>Resources</th>";
          t[++tp] = "    </thead>";
          t[++tp] = "    <tbody>";
          t[++tp] = "    </tbody>";
          t[++tp] = "  </table>";
          t[++tp] = "</div>";
          $(options.id).append(t.join(''));   // Add the table with monthlyHeader to the document
          options.period=data;
          $(options.id+" #monthlyPreviousButton").on("click",function(){changePeriod(options.period,"previous")});
          $(options.id+" #createPeriod").hide();

          $.get(options.url, { 'request':"getJson", 'period':options.period }, null,"json").done(function(data){
            // Populate the drop down list
            $.get(options.url,{'request':"getPeople",'monthly':'marinesupportoncall' },"","json").done(function(d){
              console.log("getPeople");
              $.each(d.people, function(key,value){                // For each person
                $('<option>').val(Number(key+1)).text(value.name).appendTo("#monthlyResourceSelect");// Add the name as an option
              });
            });
//            $.each(data.people, function(key,value){                                  // For each person
 //             $('<option>').val(Number(key+1)).text(value.name).appendTo("#monthlyResourceSelect");// Add the name as an option
  //          });
            $(options.id+" #monthlyResourceSelect").prepend("<option value='0' selected>Select a resource to add</option>");   
            // If the person is changed in the selector, reload the page data
            $(options.id+" #monthlyResourceSelect").on("change",function(){
              var who = $(options.id+" #monthlyResourceSelect option:selected").text();
              $(options.id+" table#monthlyFormCenter tbody:first").append("<tr id='"+who+"'><td class='right'>"+who+"</td></tr>");
              $(options.id+" #monthlyResourceSelect option:contains('"+who+"')").remove();
              $(options.id+" #createPeriod").show();
            });
            $(options.id+" #createPeriod").on("click",function(){
              var who = $(options.id+" #monthlyResourceSelect option:selected").text();
              var names =[],c = -1;
              $.each($(options.id+" #monthlyFormCenter tbody").children().get(),function(){
                names[++c] = $(this).text();
              });

              json_names = JSON.stringify({names:names}); // Convert names obj to json

              // Ajax request
              $.get(options.url, { 
                'request':"createPeriod", 
                'period':$(options.id+" #monthlyNewPeriodFirst").attr("time"),
                'days':$(options.id+" #monthlyNewPeriodLength").val(),
                'who':json_names
              }, null,"json").always(function(data){
                 // Need to update the table, re-initialise.
                 var when = $(options.id+" #monthlyNewPeriodFirst").attr("time");
                 options.period = when; // Have the new period show
                 main();
              });
            });
          });
        }).fail(function(xhr,st,err){
          console.log("Error, unable to get last month");
        });
      }

      /*
       * Calculate the totals and populate a new table section
       */ 
      function totalTable(d){
        console.log("totalTable(d)");
        $(options.summary+" #summaryTableSign").remove();  // Remove any existing table, we'll rebuild it so its up to date!
        $(options.summaryKey+" #summaryTableKey").remove();  // Remove any existing table, we'll rebuild it so its up to date!
        
        var k=Array(), kp=-1;                      // Declare an array and an index to build the HTML
        var t=Array(), tp=-1;                      // Declare an array and an index to build the HTML
        t[++tp]="<table class='table table-sm' id='summaryTableSign'>";
        t[++tp]="  <tr role='role'>";
        t[++tp]="    <th>Engineer</th>";
        t[++tp]="    <th>Sum</td>";
        t[++tp]="    <th>Initials</th>";
        t[++tp]="  </tr>";

        k[++kp]="<table class='table table-sm' id='summaryTableKey'>";
        k[++kp]="  <tr role='role'>";
        k[++kp]="    <th colspan='2'>Key</th>";
        k[++kp]="  </tr>";
        k[++kp]="  <tr role='role'>";
        k[++kp]="    <th>Initials</th>";
        k[++kp]="    <th>Name</th>";
        k[++kp]="  </tr>";
        var engineer=Array();
        $(options.id+" #monthly tbody#days tr.r").each(function(){
          console.log("Options");
          var val = Number($(this).find(".claim").text());
	  if ( 0 === val.length) {
              val = Number(0);
          }
          var eng = ($(this).find(".engineer").text());
          console.log("FIND");
	  if (!(eng in engineer)){
            console.log("IN ENG");
            var matches = eng.match(/\b(\w)/g);
            console.log(matches);
	    if (0 === matches.length) {
	        logging.debug("None for "+initials)
            } else {
                var initials = matches.join(' ');
                engineer[eng] = ({ value: val, name: initials });
                k[++kp]="  <tr role='row'><td>"+initials+"</td><td>"+eng+"</td></tr>";
            }
          } else { engineer[eng].value+= +val; }
          console.log("After ENG");
        });
        k[++kp]="</table>";
        var total=0;
        for (var key in engineer ) {
          var w = (engineer[key].name);
          var v = (engineer[key].value);
          t[++tp]="  <tr role='role'>";
          t[++tp]="    <td>"+w+"</td>";
          t[++tp]="    <td>"+v+"</td>";
          t[++tp]="    <td></td>";
          t[++tp]="  </tr>";
          total+=+v;
        }
        t[++tp]="  <tr role='role'>";
        t[++tp]="    <th>Total</th>";
        t[++tp]="    <th>"+total+"</th>";
        t[++tp]="    <th></th>";
        t[++tp]="  </tr>";
        t[++tp] = "</table>";
        $(options.summary).append(t.join(''));   // Add the table with monthlyHeader to the document
        $(options.summaryKey).append(k.join(''));   // Add the table with monthlyHeader to the document
      }
      
      /*
       * Ajax request to add person via the controller.
       */ 
      function addEntry(){
        console.log("addEntry");
        var who=$(options.id+" #newname").val();
        if (who.length > 0){
          $.get(options.url, { request:"addPerson", 'person': who, 'period': options.period, 'days': totalDays }, null,"json").done(function(d){
          initTable(d); // After adding someone, rebuild the table contents
          Reload();   // Reload the table to ensure the onclick and rules are added correctly
          });
        }
      }
      
      /*
       * Ajax request to remove a person via the controller.
       */ 
      function removeEntry(){
        console.log("removeEntry");
        var who=$(options.id+" #monthly tbody#days tr.menuActive td#title").text();
        if (who.length > 0){
          $.get(options.url, { 'request':"removePerson",'person':who,'period':options.period,'days':totalDays }, null,"json").done(function(d){
          initTable(d); // After adding someone, rebuild the table contents
          Reload();   // Reload the table to ensure the onclick and rules are added correctly
          });
        }
      }
      
      /*
       * Update the table rules.
       */ 
      function applyRules() {    
        console.log("applyRules()");
        $(options.id+" tr.r").each(function(){
          var who= $(this).attr("id");
          $(this).find("#title").remove("td");
          var editable="F";
          var CurrentEditPerson = $(options.id+" #RotaCurrentEntryEdit").attr("person");
          var auth="";
          if ($(options.id).hasAttr("edit")) {
            auth=$(options.id).attr("edit");
          }
          
          if (CurrentEditPerson==who || who.replace(" ","\ ")==auth.replace(" ", "\ ")) {
          editable="T";
          $(this).addClass("edit");
          } else {
          $(this).removeClass("edit");
          }
          $(this).attr("editable",editable);
        });
      }
      
      function loading(action){
        console.log("loading("+action+")");
        if (action=="start"){
          $(options.id).hide();
          $(options.id).after("<div id='"+options.id+"monthlyLoading'></div>");
        } else if (action=="stop"){
          $(options.id).fadeIn("slow");
          $(options.id+"monthlyLoading").remove();
        }
        // Set the style for the options background
        $(options.id+" #monthlyLoading").css({
          "border": "1px solid #8c8c8c",
          "position":"absolute",
          "display":"block",
          "background-color":"#f2f2f2",
          "top":"0",
          "left":"0",
          "width": "100%",
          "height":"100%",
          "padding":"0",
          "margin":"0",
          "-webkit-box-sizing": "border-box",
          "-moz-box-sizing": "border-box",
          "box-sizing": "border-box"
        });
      }

      /*
       * Reload the table
       */ 
      function Reload() {
        console.log("Reload()");

        $.get(options.url, { 'request':"getJson", 'period': options.period }, null,"json").done(function(d){
          updateTotals(d);
          $(options.id+" tr.r").each(function(){
          var who=$(this).attr("id");
          $.each(d.people,function(i,d){
            //
            // Calculate the totals for each day.
            // 
            $.each(d.data.booked, function(key,value){
              if (value==1){
                // Add value to total on bottom row
                day=$(options.id+" tr#dh th").eq(key).text();
                weekend=$(options.id+" tr#dh th").eq(key).attr("weekend");
                if (weekend=="yes"){
                 
                }
              }
            });
            if (who == d.name){
              $.each(d.data.booked,function(idx,data){
                who=who.replace(" ","\ ");
                r=$(options.id+" [id='"+who+"'] > #day"+idx);
                r.removeClass("available").removeClass("weekend").removeClass("holiday").removeClass("oncall").removeClass("pending");
                if (data==0) {
                  r.attr("value","F");
                  r.text("");
                  if (r.attr("weekend")=="yes") {
                    r.addClass("weekend").addClass("available");
                  } else {
                    r.addClass("available");
                  }
                } else if (data==1){
                  r.attr("value","O");
                  if (r.attr("weekend")=="yes") {
                    r.addClass("weekend").addClass("oncall");
                    r.attr("title","On Call (Weekend)");
                    r.text("1");
                  } else {
                    r.addClass("oncall");
                    r.attr("title","On Call (Weekday)");
                    r.text("0.5");
                  }
                } else {
                  r.attr("value","H");
                  if (r.attr("weekend")=="yes") {
                    r.addClass("weekend").addClass("holiday");
                    r.html("<img style='width:22px;height:22px;vertical-align:middle;' src='img/holiday.png'>");
                  } else {
                    r.addClass("holiday");
                    r.html("<img style='width:22px;height:22px;vertical-align:middle;' src='img/holiday.png'>");
                  }
                }
                });
              }
            });
          });
          console.log("Call Update Totals");
          updateTotals(d);
        });
        applyRules();
      
        $(options.id+" tr th#title").bind("mousedown", function(e) {
          if ($(this).attr("id")=="title"){
          }
        });
      }
      
      /*
       * Show a blank table that covers the table.
       */
      function BlankTable(obj) {
        console.log("BlankTable()");
        // Get the widrh and height for the gray out area we want to create
        var h=$(options.id+" #monthly").outerHeight(true);
        var w=$(options.id+" #monthly").width();
        if (h<"384px"){ h="385"; }
        if (w<"0px"){ w="100%"; }

        var tableData= Array(), ele=-1;
        tableData[++ele]="<div id='monthlyBlank'>";
        tableData[++ele]="  <table id='monthly'>";
        tableData[++ele]="    <thead id='monthlyHeader'><tr>";
        tableData[++ele]="      <th colspan='32'>"+obj.title;
        tableData[++ele]="        <div class='titlebtn' id='monthlyBlankClose'><img src='img/close.png' alt='Close'></div>";
        tableData[++ele]="      </th>";
        tableData[++ele]="    </tr></thead>";
        tableData[++ele]="  </table>";
        tableData[++ele]="</div>";
        $(options.id+" #monthly").append(tableData.join(''));// Show the form
      
        // Attach a click event to the options page to the close button
        $(options.id+" #monthlyBlankClose").on("click",function(){
          $(options.id+" #monthlyBlank").remove();
        });
      }

      /*
       * Show the settings page
       */ 
      function Settings(d) {
        console.log("Settings()");
        if (options.force == "ro"){
          return;
        } 
        BlankTable(d); // Display a grayed out area
      
        // Add Dialogue to add / remove a user
        var t=Array(), tp=-1;
        t[++tp] = "<div id='monthlyPick'>";
        t[++tp] = "  <form id='monthlyPickForm'><fieldset id='monthlySettingsLeft'>";
        t[++tp] = "    <legend>Options</legend>";
        t[++tp] = "    <p>";
        t[++tp] = "    <label class='label'>Activate Edit </label>";
        t[++tp] = "    <select class='field' id='monthlyDown'></select></li>";
        t[++tp] = "    </p>";
        t[++tp] = "    <p>";
        t[++tp] = "    <label class='label'>Add Entry </label>";
        t[++tp] = "    <input class='textbox' type='text' id='newname' name='newname' value='Add Person'></input>";
        t[++tp] = "    <input class='textbutton' id='addnewname' type='button' value='+'>";
        t[++tp] = "    </p>";
/*        t[++tp] = "  </fieldset>";
        t[++tp] = "<fieldset id='monthlySettingsRight'>";
        t[++tp] = "    <legend>Colour Theme</legend>";
        t[++tp] = "    <li><p>";
        t[++tp] = "        <label class='label'>Day Normal</label>";
        t[++tp] = "        <input class='cfield' type='color' name='monthlyDayColor' value='#ccffcc'></input>";
        t[++tp] = "        <label class='label'>Day Reserved</label>";
        t[++tp] = "        <input class='cfield' type='color' name='monthlyDayColor' value='#ccffcc'></input>";
        t[++tp] = "        <label class='label'>Day Holiday</label>";
        t[++tp] = "        <input class='cfield' type='color' name='monthlyDayColor' value='#ccffcc'></input>";
        t[++tp] = "    </p>";
        t[++tp] = "    <p>";
        t[++tp] = "        <label class='label'>Day Normal</label>";
        t[++tp] = "        <input class='cfield' type='color' name='monthlyDayColor' value='#66b3ff'></input>";
        t[++tp] = "        <label class='label'>Day Reserved</label>";
        t[++tp] = "        <input class='cfield' type='color' name='monthlyDayColor' value='#66b3ff'></input>";
        t[++tp] = "        <label class='label'>Day Holiday</label>";
        t[++tp] = "        <input class='cfield' type='color' name='monthlyDayColor' value='#66b3ff'></input>";
        t[++tp] = "    </p></li>";
        t[++tp] = "    </fieldset>";*/
        t[++tp] = "  </fieldset></form>";
        t[++tp] = "</div>";
        $(options.id+" #monthlyBlank").append(t.join(''));// Show the form
      
        // Populate the drop down list
        $.get(options.url,{'request':"getPeople",'monthly':'marinesupportoncall' },"","json").done(function(d){
          $.each(d.people, function(key,value){                // For each person
            $('<option>').val(Number(key+1)).text(value.name).appendTo("#monthlyDown");// Add the name as an option
          });
        });

//        $.each(d.people, function(key,value){                // For each person
//          $('<option>').val(Number(key+1)).text(value.name).appendTo("#monthlyDown");// Add the name as an option
//        });
        $(options.id+" #monthlyDown").prepend("<option value='0' selected>Select an entry to edit</option>");
       
        // If a new person is added, then add them!
        $(options.id+" #newname:input").bind("click", function(e) {
          if ($(this).val() == "Add Person"){
            $(this).val("");
          }
        });
        $(options.id+" #newname:input").bind("mouseout", function(e) {
          if ($(this).val().length < 1) {
           $(this).val("Add Person");
          }
        });
        $(options.id+" input#addnewname").click(function(e){ 
          if($(this).val().length >0 ){ 
           addEntry();
           $(options.id+" #monthlyDown").prepend("<option value='0' selected>"+$(options.id+" #newname:input").val()+"</option>");
           $(options.id+" #newname:input").val("Add Person");
          }
        });
      
        // If the person is changed in the selector, reload the page data
        $(options.id+" #monthlyDown").on("change",function(){ 
          $(options.id+" #RotaCurrentEntryEdit").remove();
          var who = $(options.id+" #monthlyDown option:selected").text();
          $(options.id+" #monthly").prepend("<div id='RotaCurrentEntryEdit' person='"+who+"'></div>");
          $(options.id+" #RotaCurrentEntryEdit").hide();
          Reload()
        });
      }
      
      /*
       * Change the date period being shown in the graph
       */
      function changePeriod(date,when) {
        console.log("changePeriod("+date+" , "+when+")");
        if( when == "previous" ){
          options.period=Number(date-86400); // Subtract 1 day
        } else if ( when == "next" ){
          options.period=Number(date+86400); // Add 1 day
        }
        console.log(options.period);
        $.get(options.url,{'request':"getJson",'period':options.period },"","json").done(function(d){
          $(options.id+" #monthly").remove(); // Remove any existing monthly that is already on the page for this ID
          main();
        }).fail(function(xhr,st,err){
          if (options.force == "rw") {
            document.title = "Create New Period";                                // Set the document title
            newMonth(options.period);
          } else {
            document.title = "No Period Available";                                // Set the document title
            noRotaBanner();
          }
        }); 
      }

      function errorMessage(text){
          console.log(text);
      }

      function SendMail(obj){
        var type="single";
        if (options.user=="admin"){
           type="multi";
        }
        var user_id=$(options.id).attr("uid");
        $.get(options.url,{'request':"email_monthly",'monthly':"marinesupportoncall",'type':type,'user_id':user_id,'period':options.period},null,"json").done(function(d){
          // do nothing
        }).fail(function(xhr,st,err){ 
          errorMessage("Failed to send email");
        });
         
      }

      function updateTotals(obj){
        console.log("updateTotals(obj)");
        $(options.id+" tr#totals").hide();
        $(options.id+" tr#totals").remove();
        var startDate=new Date(obj.start).getTime();
        var endDate=new Date(obj.end).getTime(),sday=Number((60*60*24)*1000);
        var tableData=Array(),ele=-1;
        var objKeys = Object.keys(obj.people).map(function(k){ 
          return obj.people[k]
        }).sort();

        var totalDays=0, totals=0;
        tableData[++ele] = "<tr id='totals'>";
        tableData[++ele] = "<td>Totals</td>";
        tableData[++ele] = "<td>-</td>";
        tableData[++ele] = "<td id=monthlyTotal></td>";
        tableData[++ele] = "<td>-</td>";

        for (day=startDate;day<=endDate;day=day+sday){

          // ---- Define variables
          var weekend="no", p=new Date(); p.setTime(day), who="";
   
          // ---- Ensure that date(p) is in UTC
	      p = new Date(p.getUTCFullYear(), p.getUTCMonth(), p.getUTCDate(), 
                        p.getUTCHours(), p.getUTCMinutes(), p.getUTCSeconds());

          // ---- Get Day/Month/Year
          var d=p.getDay(), y=p.getFullYear(), m=p.getMonth()+1, 
            dom=p.getDate(),c="",count=Number(),state="available";

          // ---- Is the day on a weekend?
          if(d==0||d==6){weekend="yes"};

          // ---- For each person
          $.each(objKeys, function(key,value){
            // ---- Is the day booked?
            if (value.data.booked[totalDays]==1){
              $("table#monthly tr.r td#"+totalDays+".engineer").text(value.name);
              var v = 0;
              if (weekend=="yes"){
                $("table#monthly tr.r td#"+totalDays+".claim").text("1");
                $("table#monthly tr.r td#"+totalDays+".claim").parent().addClass("weekend").addClass("available");
                v = 1;
              } else {
                $("table#monthly tr.r td#"+totalDays+".claim").text("0.5");
                $("table#monthly tr.r td#"+totalDays+".claim").parent().addClass("available");
                v = 0.5;
              }
              totals+=Number(v);
          }
          });

          // ---- Count the day
          ++totalDays;
        }
        tableData[++ele] = "</tr>";
        var row=tableData.join('');
        $(options.id+" tr.r:last").after(row);
        $("table#monthly tr#totals td#monthlyTotal").text(totals);
        // Rebuild the total table
        totalTable(obj);
      }

      /*
       * Build the initial table, just the date range and period for the table,
       * no data is added at this point
       */ 
      function initTable(obj) {
        console.log("initTable()");      
        $(options.id+" #monthly").remove()
        document.title = obj.title;
        tableData = new Array(), ele=-1;

        // Get the page width
        var width = $(window).width();
        // Make table responsive
        if ( width <= 786 ) {
           alert("Page to small");
        } else {
          // Get the start and the end date from the JSON data
          var startDate=new Date(obj.start).getTime();
          var endDate=new Date(obj.end).getTime(),sday=Number((60*60*24)*1000);
          var totalDays=0;
          var year_start = new Date(obj.start).getUTCFullYear();
          var year_end = new Date(obj.end).getUTCFullYear();
          var year_string = ""; 
          if (year_start == year_end) {
             year_string = year_start;
          } else {
             year_string = year_start + " - " + year_end;
          }
          tableData[++ele]="<table id='monthly' class='table table-sm table-hover table-bordered'>";
          tableData[++ele]="<thead id='monthlyHeader'>";
          tableData[++ele]="<tr role='row'><th colspan='100'><center>Towed Streamer 24hr support</center></th></tr>";
          tableData[++ele]="<tr role='row'><th>Period</th><td>"+obj.title+"</td><th>Year</th><td>"+year_string+"</td></tr>";
          tableData[++ele]="<tr role='row'><th colspan='100'>Support Phone</th></tr>";
          tableData[++ele]="</thead>";
          tableData[++ele]="<tbody id='days'><tr id='dh' role='row'>";
          tableData[++ele]="<th>Day</th>";
          tableData[++ele]="<th>Engineer</th>";
          tableData[++ele]="<th>Claim</th>";
          tableData[++ele]="<th>Comment</th>";
          for (day=startDate;day<=endDate;day=sday+day){
            var weekend="no", p=new Date();
            p.setTime(day);
            p = new Date(p.getUTCFullYear(), p.getUTCMonth(), p.getUTCDate(),
                 p.getUTCHours(), p.getUTCMinutes(), p.getUTCSeconds());
            var d=p.getDay(),y=p.getFullYear(),
                m=p.getMonth()+1,dom=p.getDate(),c="";
            if (d==0 || d==6){weekend="yes";c="weekend";}else{weekend="no"};

            

            tableData[++ele]="<tr class='r'><th class='p "+c+"' year="+y+" month="+m+" day='"+d+"' id='"+totalDays+"' weekend='"+weekend+"'>"+dom+"/"+m+"/"+y+"</th>";
            tableData[++ele]="<td class='engineer' id='"+totalDays+"'></td>";
            tableData[++ele]="<td class='claim' id='"+totalDays+"'></td>";
            tableData[++ele]="<td class='comment' contenteditable='true' id='"+totalDays+"'></td>";
            tableData[++ele]="</tr>";
            ++totalDays;
          }
          
          tableData[++ele]="<tr id='totals'><td>Totals</td><td></td><td></td><td></td></tr>";
          tableData[++ele]="</tbody></table>";  // Close the table
          console.log("Show table here");
          $(options.id).html(tableData.join(''));   // Add the table with monthlyHeader to the document.
        }

        $(options.id+" td.comment").each(function(){
          $(this).bind('blur keyup paste copy cut mouseup', function(){
            var what = $(this);
            what.trigger('change');
	  });
        });

/*	$(options.id+" td.comment").on("click", function(){
          $(this).unbind('blur keyup paste copy cut mouseup');
          setTimeout(function(){
            $(this).bind('blur keyup paste copy cut mouseup', function(){
              console.log("Change");
              $(this).trigger('change');
	    });
          },5000);
        });*/

       
        var typingTimer;
        $(options.id+" td.comment").on("change", function(){
          var what = $(this);
          clearTimeout(typingTimer);
          typingTimer = setTimeout(function(){
             saveComments();
          },5000);
	});
 
        $(options.id+" td.n").click(function(){
          var state=$(this).attr("value");
          var editable=$(this).parent().attr("editable");
          var data;
          var idx=$(this).index();
          var otherpeople=$(options.id+" tr#totals td").eq(idx).attr("state");
          var who=$(options.id+" tr#totals td").eq(idx).attr("who");
          var me=$(this).parent().attr("id");
          if (editable=="T"){
              $(this).removeClass("available").removeClass("weekend").removeClass("holiday").removeClass("oncall").removeClass("pending");
              $(this).addClass("pending");
              if(state=="F"){       data=1;
              }else if(state=="O"){ data=2;
              }else{                data=0;}
              var idx=$(this).attr("id").match(/\d+/)[0];
              var who=$(this).parent().attr("id");
              $.get(options.url, { request:"savePerson", 'person': who, 'period': options.period, 'i': idx, 'd':data }, null,"json");
          } else if (state=="O"){
            index=$(this).index();
            day=$(options.id+' tr#dh th.p').eq(index-1).text(); // Get the day for the taken cell clicked on.
            who=$(this).parent().text();        // Get the name of the person who has the day
          }
          setTimeout(function(){
          Reload();
          },100);
        });

        $(options.id+" #monthly thead#monthlyHeader tr th").append("<div class='titlebtn' id='monthlyPreviousButton'><img src='img/before.png' /></div>");
        $(options.id+" #monthly thead#monthlyHeader tr th").append("<div class='titlebtn' id='monthlyNextButton'><img src='img/after.png' /></div>");
        if (options.force=="rw") {
          $(options.id+" #monthly thead#monthlyHeader tr th").append("<div class='titlebtn' id='monthlySettingsButton'><img src='img/settings.png' alt='Settings'></div>");
          $(options.id+" #monthly thead#monthlyHeader tr th").append("<div class='titlebtn' id='monthlyMailButton'><img src='img/message.png' alt='Mail'></div>");
          $(options.id+" #monthlySettingsButton").on("click",function(){ Settings(obj);});
          $(options.id+" #monthlyMailButton").on("click",function(){ SendMail(obj);});
        }
        var startp=Math.round(Date.parse(obj.start)/1000);
        var endp=Math.round(Date.parse(obj.end)/1000);
        $(options.id+" #monthlyPreviousButton").on("click",function(){changePeriod(startp,"previous")});
        $(options.id+" #monthlyNextButton").on("click",function(){changePeriod(endp,"next")});
      }

      function saveComments() {
        var period="";
        var who="i";
        var data=[];
        var count=0;
        $(options.id+" td.comment").each(function(){
          var text=$(this).text();
          var idx=$(this).index();
          count++;
          if (text){data.push({ 'id': idx, 'comment': text })}
        });
        console.log(options.period, JSON.stringify(data));
        $.get(options.url, { request:"saveComments", 'person': who, 'period': options.period, 'd':data, 'days': count }, null,"json");
      }

      function noRotaBanner() {
        $(options.id+" #monthly").remove();
        var t=Array(), tp=-1;
        t[++tp] = "<div id='monthly'>";
        t[++tp] = "<p>Unfortunatley there is no monthly available at the moment!</p>";
        t[++tp] = "<BR/>";
        t[++tp] = "<p>Please contact your manager to start the next period.</p>";
        t[++tp] = "</div>";
        $(options.id).append(t.join(''));// Show the form
      }

      /*
       * Main function.
       */ 
      function main(){
          if (options.period=="today"){
            var tp=Math.round(Date.now()/1000);  // On Startup. look for the current period
            options.period=tp;                  // Set the period for this monthly
          }
          console.log(options.period);
          $.get(options.url,{'request':"getJson",'period':options.period },"","json").done(function(d){
            $(options.id+" #monthly").remove(); // Remove any existing monthly that is already on the page for this ID
            switch(options.action) {
              case ("monthly"):
                loading("start");
                initTable(d);                  // Create the initial table monthlyHeaders without any data
                Reload();                         // Reload the table to ensure the latest data is shown
                loading("stop");
                break;
              case ("config"):
                loading("start");
                initTable(d);                  // Create the initial table monthlyHeaders without any data
                Reload();                         // Reload the table to ensure the latest data is shown
                Settings(d);
                loading("stop");
                break;
              default:
                loading("start");
                initTable(d);                  // Create the initial table monthlyHeaders without any data
                Reload();                         // Reload the table to ensure the latest data is shown
                loading("stop");
                break;
            }  
          }).fail(function(xhr,st,err){
            if (options.force == "rw") {
              document.title = "Create New Period";                                // Set the document title
              newMonth(options.period);
            } else {
              document.title = "No Period Available";                                // Set the document title
              noRotaBanner();
            }
          });

      }
      main();
      return;
    }
  });
}( jQuery ));
$("#month1").monthly({ id:"#month1", summary: "#month1sum", summaryKey: "#month1sumkey" });
$("#monthnoedit").monthly({ id: "#monthnoedit", summary: "#monthnoeditsum", summaryKey: "#monthnoeditsumkey",  force: "ro" });
