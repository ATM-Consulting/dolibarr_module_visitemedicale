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
		[onshow;block=begin;when [view.mode]=='new']
			<td style="width: 20%;">Délai avant la prochaine visite (en mois)</td>
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='new']
			<td style="width: 20%;">Date prévue pour la prochaine visite</td>
		[onshow;block=end]
		
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

[onshow;block=begin;when [view.mode]=='new']
<div class="tabsAction" style="text-align: center;">
	<input type="submit" value="Enregistrer" name="save" class="button">
</div>
[onshow;block=end]

[onshow;block=begin;when [view.mode]=='view']
<div class="tabsAction" style="text-align: center;">
	<a href="visitemedicale.php?action=new&id=[visite.rowid;strconv=no]" class="butAction">Plannifier la prochaine visite</a>
	<a href="visitemedicale.php?action=edit&id=[visite.rowid;strconv=no]" class="butAction">Modifier</a>
</div>
[onshow;block=end]

[onshow;block=begin;when [view.mode]=='edit']
<div class="tabsAction" style="text-align: center;">
	<input type="submit" value="Modifier" name="save" class="button">
	<input type="submit" value="Annuler" name="cancel" class="button">
</div>
[onshow;block=end]