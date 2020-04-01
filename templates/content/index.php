<?php
style('miage-nextcloud-traces', 'style');
style('miage-nextcloud-traces', 'datatable.min');
script('miage-nextcloud-traces', 'datatable.min');
?>
<div class="date-filter-block">
	<span>
		<input id="min" name="min" type="text" placeholder="From">
	</span>
	<span>
		<input id="max" name="max" type="text" placeholder="To">
	</span>
</div>
<table id="datatable" class="table table-striped">
	<thead>	
		<tr>
			<th scope="col">Date</th>
			<th scope="col">User</th>
			<th scope="col">Action</th>
			<th scope="col">Affected to</th>
			<th scope="col">Path/Name</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($_['response'] as $row){ ?>
			<tr>
       			<td><?php p($row['timestamp']); ?></td>
        		<td><?php p($row['user']); ?></td>
				<td><?php p($row['subject']); ?></td>
				<td><?php p($row['affecteduser']);?></td>
				<td><?php p($row['file']); ?></td>
			</tr>
			<?php 
		}
		?>
	</tbody>
	<tfoot>
		<tr>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
		</tr>
	</tfoot>
</table>