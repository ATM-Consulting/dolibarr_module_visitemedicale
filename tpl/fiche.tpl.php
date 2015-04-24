<table class="border" width="100%">
	<tr>
		<td style="width: 20%;">Utilisateur concerné</td>
		<td>[visite.user;strconv=no]</td>
	</tr>
	<tr>
		<td style="width: 20%;">Date de la visite</td>
		<td>[visite.date_visite;strconv=no]</td>
	</tr>
	<tr>
		<td style="width: 20%;">Délai avant la prochaine visite</td>
		<td>[visite.delai_next_visite;strconv=no] mois</td>
	</tr>
	<tr>
		<td style="width: 20%;">Date prévisionnelle pour la prochaine visite</td>
		<td>[visite.date_next_visite;strconv=no]</td>
	</tr>
	<tr>
		<td style="width: 20%;">Type de visite médicale</td>
		<td>[visite.type;strconv=no]</td>
	</tr>
	<tr>
		<td style="width: 20%;">Personnel</td>
		<td>[visite.personnel;strconv=no]</td>
	</tr>
	<tr>
		<td style="width: 20%;">Commentaire</td>
		<td>[visite.commentaire;strconv=no]</td>
	</tr>
</table>

<script>
	$('#delai_next_visite').keyup(function() {
		var delai = $(this).val();
		var date_visite = $('#date_visite').val();
		var infos = date_visite.split('/');
		
		var d = new Date(parseInt(infos[2]), parseInt(infos[1]), parseInt(infos[0]));
		d.setMonth(d.getMonth() + (delai - 1));

		$('#date_next_visite').datepicker('setDate', d);
	});
</script>
