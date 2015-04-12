<table class="table-bordered table-striped table display" id="inquiries">
		<caption>
			<div class="row-fluid">
				<div class="span6">
					<?php if (!$show_hidden): ?>
					<h1>Lista zapytań</h1>
					<?php else: ?>
					<h1>Lista ukrytych zapytań</h1>
					<?php endif; ?>
				</div>
				<?php if ($user_type == 'user'): ?>
				<div class="span6">
					<?php if ($show_hidden): ?>
					<a href="<?php echo URL::base(); ?>inquiry" class="btn btn-warning btn-mini pull-right hidden-toggle">Powrót</a>
					<?php else: ?>
					<a href="<?php echo URL::base(); ?>inquiry/index/show-hidden" class="btn btn-warning btn-mini pull-right hidden-toggle">Pokaż ukryte</a>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
		</caption>

	<thead>
		<tr>
			<th>&nbsp;</th>
			<th class="">Data</th>
			<th class="span3">Imię i nazwisko</th>
			<th class="span3">Email</th>
			<th class="">Telefon</th>
			<th class="">Kontakt</th>
			<th class="span4">Treść</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($inquiries as $i): ?>
		<tr data-id="<?php echo $i->inquiry_id; ?>" id="row-<?php echo $i->inquiry_id; ?>">
			<td>
				<div class="btn-group">
					<a class="btn btn-mini dropdown-toggle" href="" data-toggle="dropdown">
						<i class="icon-cog"></i>
						<span class="caret"></span>	
					</a>
					<?php if ($user_type == 'admin'): ?>
					<ul class="dropdown-menu">
						<li><a href="#" class="edit">Edytuj</a></li>
						<li><a href="#" class="remove">Usuń</a></li>
					</ul>
					<?php else: ?>
					<ul class="dropdown-menu">
						<?php if (!$show_hidden): ?>
						<li><a href="#" class="_hide">Ukryj</a></li>
						<?php else: ?>
						<li><a href="#" class="_show">Przywróć</a></li>
						<?php endif; ?>
					</ul>
					<?php endif; ?>
				</div>
				
			</td>
			<td><?php echo date('d.m.Y', strtotime($i->date_added)); ?></td>
			<td><?php echo $i->name; ?></td>
			<td><?php echo $i->email; ?></td>
			<td><?php echo $i->phone; ?></td>
			<td>
			<?php 
				$contact = array();
				if ($i->contact_email) $contact[] = 'email';
				if ($i->contact_phone) $contact[] = 'telefon';

				echo count($contact) > 0 ? implode(', ', $contact) : 'brak';
			 ?></td>
			<td>
				<?php echo nl2br($i->inquiry); ?>
				<?php if (!empty($i->attachment)): ?>
					<a href="<?php echo URL::base() . $i->attachment;?>" target="_blank" class="btn btn-mini btn-warning"><i class="icon-file"></i></a>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>


<div id="inquiry_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modal_label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="modal_label">Zapytanie</h3>
	</div>
	<div class="modal-body">
		<form id="inquiry_form" data-empty-error="Wymagane pole" data-type-error="Błędny format danych" method="POST">
			<input type="hidden" name="inquiry_id" value="" id="inquiry_id">
			<div class="control-group">
				<label for="name">Imię i nazwisko:</label>
				<div class="controls">
					<input type="text" id="name" name="name" class="input-block-level validate">
				</div>
			</div>
			<div class="control-group">
				<label for="email">Email:</label>
				<div class="controls">
					<input type="text" id="email" name="email" class="input-block-level validate" data-type="email">
				</div>
			</div>
			<div class="control-group">
				<label for="phone">Telefon:</label>
				<div class="controls">
					<input type="text" id="phone" name="phone" class="input-block-level validate" data-empty="ok" data-type="phone">
				</div>
			</div>
			<div class="control-group">
				<label for="">Preferowany kontakt:</label>
				<div class="controls">
					<label class="checkbox">
						<input type="checkbox" name="contact_email" class="validate" data-type="checkbox-group" value="1" data-after="#second_checkbox" data-empty-error="Wybierz minimum jedną z opcji"> Email
					</label>
					<label class="checkbox" id="second_checkbox">
						<input type="checkbox" name="contact_phone" class="validate" data-type="checkbox-group" value="1" data-after="#second_checkbox" data-empty-error="Wybierz minimum jedną z opcji"> Telefon
					</label>
				</div>
			</div>
			<div class="control-group">
				<label for="inquiry">Zapytanie:</label>
				<div class="controls">
					<textarea id="inquiry" name="inquiry" class="input-block-level validate" rows="6"></textarea>
				</div>
			</div>
			<p id="attachment_container">Załącznik: <b id="attachment"></b></p>
			<input type="file" name="attachment" title="Wybierz załącznik">
		</form>
		<br />
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Anuluj</button>
		<button class="btn btn-primary" id="modal_submit">Dodaj</button>
	</div>
</div>
	