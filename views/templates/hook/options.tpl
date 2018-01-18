<div style="display: none" id="additionalOptions" class="option_choices col-md-12">	
	<p>Vous souhaitez réserver également un casque de vélo, une remorque pour enfant ou un siège bébé ?</p>
	<form method="post" action="{$module_dir|escape:'html':'UTF-8'}forms/options_form.php">
		<div class="col-md-4">
			<div class="col-md-2">
				<input type="checkbox" name="location[]" value="casque" id="casque" /> 
			</div>
			<label for="casque" style="margin-left: 1%;" class="col-md-9">
				<img src="http://www.dropnride.fr/39-small_default/velo-demi-journee.jpg" class="col-md-5"/>
				<span class="col-md-6">Casque de Vélo</span>
			</label>
		</div>
		<div class="col-md-4">
			<div class="col-md-2">
				<input type="checkbox" name="location[]" value="carriole" id="carriole" />
			</div>
			 <label for="carriole" style="margin-left: 1%;" class="col-md-9">
			 	<img src="http://www.dropnride.fr/36-small_default/velo-demi-journee.jpg" class="col-md-5"/>
			 	<span class="col-md-6">Remorque pour Enfant</span>
			 </label>
		</div>
		<div class="col-md-4">
			<div class="col-md-2">
				<input type="checkbox" name="location[]" value="siege" id="siege" />
			</div>
			<label for="siege" style="margin-left: 1%;" class="col-md-9">
				<img src="http://www.dropnride.fr/40-small_default/velo-demi-journee.jpg" class="col-md-5"/>
				<span class="col-md-6">Siège pour Bébé</span>
			</label>
		</div>
		<br />
		<div class="col-md-offset-4 col-md-4" style="text-align: center;">
			<input type="submit" name="bouton" value="Rajouter ces options" class="submit"/>
		</div>
	</form>
</div>

{literal}
<script>
	function strncmp(a, b, n) {
    	return (a.substring(0, n) == b.substring(0, n))
	}

	window.onload = function () {
		var x = document.getElementById('cart_summary').childNodes[5].childNodes

		x.forEach(function (element) {
			if (element.tagName != 'TR')
				return
			if (strncmp(element.id, 'product_14', 'product_14'.length))
				return
			document.getElementById('additionalOptions').setAttribute('style', 'display: inline')
		})
	}

	function blockButtonToMax(element, type) {
		var id = element.id
		id = id.substr(id.indexOf('_') + 1, id.length)
		var secondPart = id.substr(id.indexOf(type) + type.length, id.length)
		id = id.substr(0, id.indexOf('_'))
		id = id + secondPart
		console.log(id)
		var myQuantity = document.getElementById(id).value
		type == 'up' ? myQuantity++ : myQuantity--
		var plusElement = element.id.split('down').join('up');
		if (myQuantity >= 4)
			document.getElementById(plusElement).setAttribute('disabled', true)
		else
			document.getElementById(plusElement).removeAttribute('disabled')
	}

</script>
{/literal}
