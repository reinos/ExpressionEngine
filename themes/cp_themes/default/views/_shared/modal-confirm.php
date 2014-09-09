<div class="col-group">
	<div class="col w-16 last">
		<a class="m-close" href="#"></a>
		<div class="box">
			<h1><?=lang('confirm_removal')?></h1>
			<?=form_open($form_url, 'class="settings"', (isset($hidden)) ? $hidden : array())?>
				<div class="alert inline issue">
					<p><?=lang('confirm_removal_desc')?></p>
				</div>
				<div class="txt-wrap">
					<ul class="checklist">
						<?php foreach ($checklist as $item): ?>
						<li><?=$item['kind']?>: <b><?=$item['desc']?></b></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<fieldset class="form-ctrls">
					<?=cp_form_submit('btn_confirm_and_remove', 'btn_confirm_and_remove_working')?>
				</fieldset>
			</form>
		</div>
	</div>
</div>