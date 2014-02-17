<table class="cntrct_detail">
	<tr class="detail_row_data">
		<td class="label">Customers:</td>
		<td class="data">
			<div class="scrollY">
			<?php foreach ($contract->get_customers() as $customer): ?>
				<?php echo $customer->get_cust_nm().' ('.$customer->get_cust_tp().': '.$customer->get_cust_cd().')'; ?><br />
			<?php endforeach; ?>
			</div>
		</td>
		<td class="label">Divisions:</td>
		<td class="data">
			<?php 
				$div_cnt = 0;
				foreach ($this->location->get_by_div() as $div_cd => $div_data)
				{
					if ($div_cnt > 0) echo "<br />";
					
					echo $div_data['name'].":&nbsp;&nbsp;(";
					$count = 0;
					foreach ($div_data['sls_ctrs'] as $sls_ctr_cd => $sls_ctr_data)
					{
						if ($count > 0) echo ", ";
						echo $sls_ctr_data['short_name'];
						$count++;
					}
					echo ')';
					$div_cnt++;
				}			
			?>
		</td>
	</tr>
</table>