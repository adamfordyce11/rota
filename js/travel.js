(function($){
  $.fn.extend({
    travel:function(options){
      console.log("travel()");
      var defaults = {
        id: this.selector
      };
      var options = $.extend(defaults,options);

      var tableData = new Array(), ele=-1;                           // Create an array to store the table inside
      tableData[++ele] = "<p>Test</p>";
      tableData[++ele] = "<p>Test1</p>";
      tableData[++ele] = "<p>Test3</p>";
      console.log(this.selector);
      $(options.id).html(tableData.join(''));
    }
  });
})( jQuery );

$("#eng1").travel();
