<p>Bonjour, <br />Nous avons bien reçu votre paiement.</p>
<p>Afin de pouvoir retirer votre vélo, vous devez récupérer sa batterie dans un casier. Pour ouvrir ce casier vous aurez besoin d'un digicode.</p>
<p>Comment souhaitez-vous recevoir votre digicode ?</p>
<form method="post" action="{$module_dir|escape:'html':'UTF-8'}forms/sms_form.php">
	<input type="checkbox" name="type_envoi[]" value="sms" id="sms" /> <label for="sms" class="col-md-11" style="margin-left: 1%;">Par SMS</label>
	<input type="checkbox" name="type_envoi[]" value="mail" id="mail" class="col-md-1" /> <label for="mail" class="col-md-11" style="margin-left: 1%;">Par e-mail</label>
	<br />
	<input type="hidden" name="cart" value="{$smarty.get.cart}" />
	<input type="submit" name="bouton" value="Obtenir votre digicode" />
</form>