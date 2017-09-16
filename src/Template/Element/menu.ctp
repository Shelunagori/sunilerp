<?php

if(!isset($active_menu))
{
    $active_menu = '';
}
?>
<?php 
echo '<li>'.$this->Html->link($this->Html->tag('i', '', ['class' => 'icon-home']).'Dashboard', '/Users/Dashboard',['escape' => false]).'</li>';
?>

<li class="start ">
	<a href="javascript:;">
	<i class="fa fa-building-o"></i>
	<span class="title">GRN</span>
	<span class="arrow "></span>
	</a>
	<ul class="sub-menu">
		<li><?php echo $this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-plus-square']).' Create', '/Grns/Add',['escape' => false]); ?></li>
		<li><?php echo $this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-list-ul']).' List', '/Grns',['escape' => false]); ?></li>
	</ul>
</li>

<li class="start ">
	<a href="javascript:;">
	<i class="fa fa-cog"></i>
	<span class="title">Stock Journals</span>
	<span class="arrow "></span>
	</a>
	<ul class="sub-menu">
		<li><?php echo $this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-plus-square']).' Create', '/StockJournals/Add',['escape' => false]); ?></li>
		<li><?php echo $this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-list-ul']).' List', '/StockJournals',['escape' => false]); ?></li>
	</ul>
</li>

<li class="start ">
	<a href="javascript:;">
	<i class="fa fa-ticket"></i>
	<span class="title">Purchase Voucher</span>
	<span class="arrow "></span>
	</a>
	<ul class="sub-menu">
		<li><?php echo $this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-plus-square']).' Create', '/PurchaseVouchers/Add',['escape' => false]); ?></li>
		<li><?php echo $this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-list-ul']).' List', '/PurchaseVouchers',['escape' => false]); ?></li>
	</ul>
</li>
<li class="start ">
	<a href="javascript:;">
	<i class="fa fa-square"></i>
	<span class="title">Sales Invoice</span>
	<span class="arrow "></span>
	</a>
	<ul class="sub-menu">
		<li><?php echo $this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-plus-square']).' Create', '/SalesInvoices/Add',['escape' => false]); ?></li>
		<li><?php echo $this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-list-ul']).' List', '/SalesInvoices',['escape' => false]); ?></li>
	</ul>
</li>
<li class="start ">
	<a href="javascript:;">
	<i class="fa fa-square"></i>
	<span class="title">Credit Note</span>
	<span class="arrow "></span>
	</a>
	<ul class="sub-menu">
		<li><?php echo $this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-plus-square']).' Create', '/CreditNotes/Add',['escape' => false]); ?></li>
		<li><?php echo $this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-list-ul']).' List', '/CreditNotes',['escape' => false]); ?></li>
	</ul>
</li>
<li class="start ">
	<a href="javascript:;">
	<i class="fa fa-square"></i>
	<span class="title">Inter Location Stock Transfer Vouchers</span>
	<span class="arrow "></span>
	</a>
	<ul class="sub-menu">
		<li><?php echo $this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-plus-square']).' Create', '/IntraLocationStockTransferVouchers/Add',['escape' => false]); ?></li>
		<li><?php echo $this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-list-ul']).' List', '/IntraLocationStockTransferVouchers',['escape' => false]); ?></li>
	</ul>
</li>

<?php 
echo '<li>'.$this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-cogs']).'Masters & Setup', '/Users/masterSetup',['escape' => false]).'</li>';
?>
<?php 
echo '<li>'.$this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-edit']).'Reports', '/Users/reports',['escape' => false]).'</li>';
?>
<?php 
echo '<li>'.$this->Html->link($this->Html->tag('i', '', ['class' => 'fa fa-lock']).'Logout', '/Users/logout',['escape' => false]).'</li>';
?>