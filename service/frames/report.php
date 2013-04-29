<?php include "header.php"; ?>


<script>

$(document).ready(function(){
   mw.$("#bug-form").submit(function(){
     mw.post(this, function(){
       
     });
      return false;
   });
});

</script>

<div id="report-frame" class="frame">


<span class="ico ireport"></span>
<div>
  <span id="username"></span>, report the Bug. <br>
  Please describe that you find in details, so we can fix it.
</div>



<form action="query.php" class="form" id="bug-form">
    <div class="mw-ui-field-holder">
        <input type="text" class="mw-ui-field" placeholder="Your Email" name="Email" />
    </div>
    <div class="mw-ui-field-holder">
        <input type="text" class="mw-ui-field" placeholder="Bug Title" name="Title" />
    </div>

    <div class="mw-ui-field-holder">
        <textarea class="mw-ui-field" placeholder="Describe your issue" name="Message"></textarea>
    </div>

    <input type="hidden" name="Subject" value="Report a Bug" />

    <input type="submit" value="Send Message" class="mw-ui-btn mw-ui-btn-blue right" />

</form>



</div>







<?php include "footer.php"; ?>