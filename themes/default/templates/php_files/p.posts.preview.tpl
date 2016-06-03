<div id="preview" class="box_title">
	{$psPreview.titulo}
</div>
<div id="preview" class="box_cuerpo">
  	{$psPreview.cuerpo}
</div>
{literal}
  <script type="text/javascript">
    $(window).bind('resize',function(){
      $('#preview').height((document.documentElement.clientHeight - 200) + 'px');
      myActions.style();
    });
    //$(window).trigger('resize');
  </script>
{/literal}
