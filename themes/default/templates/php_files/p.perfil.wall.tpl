1:
{if $psPrivacidad.m.v == true}
	{include file='modulos/m.perfil_muro.tpl'}
	<script type="text/javascript">
	/* {literal} */
	$(function(){
	    $('#wall, #attaDesc').css('max-height', '300px');
	    setTimeout("$('#wall, #attaDesc').blur().css('height', '14px')",0);
	    setTimeout("$('#attaContent input').blur().css('height', '14px')",0);
	    // WALL
	    $('#wall').focus(function(){
	        $('.btnStatus').show();
	        $('.frameForm').css('border-bottom', '1px solid #E9E9E9');
	    });
	});
	/* {/literal} */
	</script>
{/if}