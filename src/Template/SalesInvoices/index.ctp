<?php
/**
 * @Author: PHP Poets IT Solutions Pvt. Ltd.
 */
$this->set('title', 'Sales Invoice List');
?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet light ">
			<div class="portlet-title">
				<div class="caption">
					<i class="icon-bar-chart font-green-sharp hide"></i>
					<span class="caption-subject font-green-sharp bold ">Sales Invoice</span>
				</div>
			</div>
			<div class="portlet-body">
				<div class="table-responsive">
					<?php $page_no=$this->Paginator->current('salesInvoices'); $page_no=($page_no-1)*20; ?>
					<table class="table table-bordered table-hover table-condensed">
						<thead>
							<tr>
								<th scope="col"><?= __('Sr') ?></th>
								<th scope="col"><?= $this->Paginator->sort('voucher_no') ?></th>
								<th scope="col"><?= $this->Paginator->sort('party_ledger_id') ?></th>
								<th scope="col"><?= $this->Paginator->sort('transaction_date') ?></th>
								<th scope="col"><?= $this->Paginator->sort('amount_after_tax') ?></th>
								<th scope="col" class="actions"><?= __('Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($salesInvoices as $salesInvoice): ?>
							<tr>
								<td><?= h(++$page_no) ?></td>
								<td><?= h('#'.str_pad($salesInvoice->voucher_no, 4, '0', STR_PAD_LEFT)) ?></td>
								<td><?= h($salesInvoice->party_ledger->name) ?></td>
								<td><?= h($salesInvoice->transaction_date) ?></td>
								<td class="rightAligntextClass"><?= h($salesInvoice->amount_after_tax) ?></td>
								<td class="actions">
									
									<?= $this->Html->link(__('Edit'), ['action' => 'edit', $salesInvoice->id]) ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<?= $this->Html->link(__('View Bill'), ['action' => 'sales_invoice_bill', $salesInvoice->id]) ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<div class="paginator">
					<ul class="pagination">
						<?= $this->Paginator->first('<< ' . __('first')) ?>
						<?= $this->Paginator->prev('< ' . __('previous')) ?>
						<?= $this->Paginator->numbers() ?>
						<?= $this->Paginator->next(__('next') . ' >') ?>
						<?= $this->Paginator->last(__('last') . ' >>') ?>
					</ul>
					<p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
				</div>
			</div>
		</div>
	</div>
</div>