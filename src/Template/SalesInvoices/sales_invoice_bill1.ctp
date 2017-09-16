<style>

@media print{
	.maindiv{
		width:300px !important;
	}	
	.hidden-print{
		display:none;
	}
}
p{
margin-bottom: 0;
}
.table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
    padding: 5px !important;
	font-family:Lato !important;
}
</style>

<style type="text/css" media="print">
@page {
    size: auto;   /* auto is the initial value */
    margin: 0px 0px 0px 0px;  /* this affects the margin in the printer settings */
}
</style>
<div style="width:300px;" class="maindiv">
<table  width="100%" border="0" >
<tbody>
<?php foreach($invoiceBills->toArray() as $data){
		foreach($data->sales_invoice_rows as $sales_invoice_row){?>
			<?php }}?>
	<tr>
		<td colspan="4"
		style="text-align:center;font-size:18px;"><b><span><?=@$data->company->name?></span></b></td>
    </tr>
	
	
	<tr><td colspan="4"
 		style="text-align:center;font-size:12px !important;"><span><?=@$data->company->address?>, <?=@$data->company->state->name?></span></td>
	</tr>
	<tr><td colspan="4"
 		style="text-align:center;font-size:12px !important;"><span>Ph - <?=@$data->company->phone_no ?> Mobile - <?=@$data->company->mobile ?><br> GSTIN NO:
		<?=@$data->company->gstin ?></span></td>
	</tr>
	<tr>
		<td colspan="4"
		style="text-align:center;font-size:16px; padding-bottom:10px;  padding-top:10px;"><b><span><u>GST INVOICE</u></span></b></td>
	</tr>
	<tr>
		<td colspan="4" style="text-align:center;font-size:14px;"><b>Customer Name: <?=ucwords($data->partyDetails->name)?> (<?=$data->partyDetails->mobile?> )</b></td>
	</tr>
	<tr>
		<td colspan="4"
		style=" padding-bottom:10px;  padding-top:10px;"></td>
	</tr>
	<tr>
		<td><b>Item Code</b></td>
		<td align="center"><b>Size</b></td>
		<td align="center"><b>Qty</b></td>
		<td align="center"><b>Rate</b></td>
	</tr>
	<tr>
		<td style="font-style:italic;font-size:14px;"><b>HSN Code</b></td>
		<td align="center" style="font-style:italic;font-size:14px;"><b>Dis %</b></td>
		<td><b></b></td>
		<td align="center" style="font-style:italic;font-size:14px;"><b>Net Amount</b></td>
	</tr>
	<?php if($taxable_type!= 'IGST') { ?>
	<tr>
		<td></td>
		<td align="center" style="font-style:italic;font-size:14px;">Taxable Value</td>
		<td align="center" style="font-style:italic;font-size:14px;"> %SGST </br> %CGST</td>
		<td></td>
	</tr>
	<?php } else { ?> 
	<tr>
		<td></td>
		<td style="font-style:italic;font-size:14px;">Taxable Value</td>
		<td style="font-style:italic;font-size:14px;">%IGST</td> <?php } ?>
	</tr>
	<?php
		
		foreach($invoiceBills->toArray() as $data){
		$cgst=0;
		$sgst=0;
		$igst=0;
		$totalAmount=0;
		
		foreach($data->sales_invoice_rows as $sales_invoice_row){
			if(@$data->company->state_id==$data->partyDetails->state_id){
			$gst_type=$sales_invoice_row->gst_figure->tax_percentage;
			$gst_perc=$gst_type/2;
			$gstValue=$sales_invoice_row->gst_value;
			$gst=$gstValue/2;
			$cgst+=$gst;
			$sgst+=$gst;
			$totalAmount+=$sales_invoice_row->quantity*$sales_invoice_row->rate;
			}
			else{
			$gst_type=$sales_invoice_row->gst_figure->name;
			$gstValue=$sales_invoice_row->gst_value;
			$gst=$gstValue;
			$igst+=$gst;
			}
		?>
		<tr><td colspan="4" style="border-top:1px dashed;"></td></tr>
		<tr>
			<td><?=$sales_invoice_row->item->name ?></td>
			<td><?php
			if(!empty($sales_invoice_row->item->size->name))
			{
			echo @$sales_invoice_row->item->size->name;
			}
			else{
			echo '-';
			}
			?></td>
			<td style="text-align:right;"><?=$sales_invoice_row->quantity ?></td>
			<td style="text-align:right;"><?=$sales_invoice_row->rate ?></td>
		</tr>
		<tr>
			<td style="font-style:italic;font-size:14px;"><?=$sales_invoice_row->item->hsn_code ?></td>
			<td style="font-style:italic;font-size:14px;text-align:right"><?=$sales_invoice_row->discount_percentage ?></td>
			<td></td>
			<td style="font-style:italic;font-size:14px;text-align:right"><?=$sales_invoice_row->net_amount ?></td>
		</tr>
		
		<?php if($data->company->state_id==$data->partyDetails->state_id){?>
		<tr>
			<td></td>
			<td style="font-style:italic;font-size:14px;text-align:right"><?=$sales_invoice_row->taxable_value ?></td>
			<td style="font-style:italic;font-size:14px;text-align:right"><?=$gst_perc.' %' ?><br/><?=$gst_perc.' %'?></td>
			<td></td>
		</tr>
		
		<?php }else {?>
		<tr>
			<td></td>
			<td style="font-style:italic;font-size:14px;text-align:right"><?=$sales_invoice_row->taxable_value ?></td>
			<td style="font-style:italic;font-size:14px;text-align:right"><?=$gst_type ?></td>
			<td></td>
		</tr>
		<?php }?>
		<?php }} ?>
		<tr><td colspan="4" style="border-top:1px dashed;"></td></tr>
		
		<tr>
			<td>Total MRP</td>
			<td></td>
			<td></td>
			<td style="text-align:right;"><?php echo number_format($totalAmount,2);  ?></td>
			</tr>
		<tr>
			<td>Discount </td>
			<td></td>
			<td></td>
			<td style="text-align:right;"><?php echo number_format($totalAmount-$data->amount_after_tax, 2);  ?></td>
		</tr>
		<tr>
			<td>Net Total</td>
			<td></td>
			<td></td>
			<td style="text-align:right;"><?php echo number_format($data->amount_after_tax, 2);  ?></td>
		</tr>
				
</tbody></table>
<table width="100%" border="1px" style="font-size:12px; border-collapse: collapse; margin-top:15px;">
<thead>
	<?php if($taxable_type!= 'IGST') { ?>
	<tr>
		<td>Taxable Value</td>
		<td>CGST (%)</td>
		<td>CGST Amount</td>
		<td>SGST (%)</td>
		<td>SGST Amount</td>
	</tr>
</thead>
<tbody>
	<?php } else { ?>
	<tr>
		<td>Taxable Value</td>
		<td>IGST(%)</td>
		<td>IGST Amount</td>
	</tr>
	<?php } ?>
	<?php foreach($sale_invoice_rows as $sale_invoice_row){
	if($taxable_type!= 'IGST') { ?>
	<tr>
		<td style="text-align:right;"><?= h($sale_invoice_row->total_taxable_amount) ?></td>
		<td style="text-align:right;"><?= h($sale_invoice_row->gst_figure->tax_percentage/2) .'%' ?></td>
		<td style="text-align:right;"><?= h($sale_invoice_row->total_gst_amount/2) ?></td>
		<td style="text-align:right;"><?= h($sale_invoice_row->gst_figure->tax_percentage/2) .'%' ?></td>
		<td style="text-align:right;"><?= h($sale_invoice_row->total_gst_amount/2) ?></td>
	</tr>
	<?php } else { ?>
	<tr>
		<td style="text-align:right;"><?= h($sale_invoice_row->total_taxable_amount) ?></td>
		<td style="text-align:right;"><?= h($sale_invoice_row->gst_figure->tax_percentage).'%' ?></td>
		<td style="text-align:right;"><?= h($sale_invoice_row->total_gst_amount) ?></td>
	</tr>
	<?php } } ?>
</tbody>
</table>
</div>
