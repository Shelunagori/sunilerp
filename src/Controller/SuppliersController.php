<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Suppliers Controller
 *
 * @property \App\Model\Table\SuppliersTable $Suppliers
 *
 * @method \App\Model\Entity\Supplier[] paginate($object = null, array $settings = [])
 */
class SuppliersController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
		$this->viewBuilder()->layout('index_layout');
        $this->paginate = [
            'contain' => ['States']
        ];
        $suppliers = $this->paginate($this->Suppliers->find());

        $this->set(compact('suppliers'));
        $this->set('_serialize', ['suppliers']);
    }

    /**
     * View method
     *
     * @param string|null $id Supplier id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $supplier = $this->Suppliers->get($id, [
            'contain' => ['States', 'Ledgers']
        ]);

        $this->set('supplier', $supplier);
        $this->set('_serialize', ['supplier']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
		$this->viewBuilder()->layout('index_layout');
        $supplier = $this->Suppliers->newEntity();
		$company_id=$this->Auth->User('session_company_id');
		$this->request->data['company_id'] =$company_id;
        if ($this->request->is('post')) {
            $supplier = $this->Suppliers->patchEntity($supplier, $this->request->getData());
			
            if ($this->Suppliers->save($supplier)) {
				//Create Ledger//
			$accounting_group = $this->Suppliers->Ledgers->AccountingGroups->find()->where(['company_id'=>$company_id,'supplier'=>1])->first();
			
				$ledger = $this->Suppliers->Ledgers->newEntity();
				$ledger->name = $supplier->name;
				$ledger->accounting_group_id = $accounting_group->id;
				$ledger->company_id=$company_id;
				$ledger->supplier_id=$supplier->id;
				$ledger->bill_to_bill_accounting='no';
				if($this->Suppliers->Ledgers->save($ledger))
				{
					//Create Accounting Entry//
			        $transaction_date=$this->Auth->User('session_company')->books_beginning_from;
					$AccountingEntry = $this->Suppliers->Ledgers->AccountingEntries->newEntity();
					$AccountingEntry->ledger_id        = $ledger->id;
					if($supplier->debit_credit=="Dr")
					{
						$AccountingEntry->debit        = $supplier->opening_balance_value;
					}
					if($supplier->debit_credit=="Cr")
					{
						$AccountingEntry->credit       = $supplier->opening_balance_value;
					}
					$AccountingEntry->customer_id      = $supplier->id;
					$AccountingEntry->transaction_date = date("Y-m-d",strtotime($transaction_date));
					$AccountingEntry->company_id       = $company_id;
					$AccountingEntry->is_opening_balance = 'yes';
					if($supplier->opening_balance_value){
					$this->Suppliers->Ledgers->AccountingEntries->save($AccountingEntry);
					}
				}
                $this->Flash->success(__('The supplier has been saved.'));

                return $this->redirect(['action' => 'add']);
            }
            $this->Flash->error(__('The supplier could not be saved. Please, try again.'));
        }
		$SundryDebtor = $this->Suppliers->Ledgers->AccountingGroups->find()->where(['supplier'=>1,'company_id'=>$company_id])->first();
		$accountingGroups = $this->Suppliers->Ledgers->AccountingGroups
							->find('children', ['for' => $SundryDebtor->id])
							->find('List')->toArray();
		$accountingGroups[$SundryDebtor->id]=$SundryDebtor->name;
		ksort($accountingGroups);
        $states = $this->Suppliers->States->find('list',
												['keyField' => function ($row) {
													return $row['id'];
												},
												'valueField' => function ($row) 
												{
													if($row['state_code']<=9)
														{
															return str_pad($this->_properties['state_code'], 1, '0', STR_PAD_LEFT).$row['state_code'].'-'. $row['name'] ;
														}
														else
														{
															return $row['state_code'].'-'. $row['name'] ;
														}
												}]);
        $this->set(compact('supplier', 'states','accountingGroups'));
        $this->set('_serialize', ['supplier']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Supplier id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
		$this->viewBuilder()->layout('index_layout');
        $supplier = $this->Suppliers->get($id, [
            'contain' => ['Ledgers']
        ]); 
		//pr($supplier);exit;
		$company_id=$this->Auth->User('session_company_id');
        if ($this->request->is(['patch', 'post', 'put'])) {
            $supplier = $this->Suppliers->patchEntity($supplier, $this->request->getData());
            if ($this->Suppliers->save($supplier)) {
				$ledger = $this->Suppliers->Ledgers->get($supplier->ledger->id);
				$ledger->name=$supplier->name;
				$ledger->accounting_group_id=$supplier->accounting_group_id;
				$this->Suppliers->Ledgers->save($ledger);
					
				//Accounting Entry
					$query_delete = $this->Suppliers->Ledgers->AccountingEntries->query();
					$query_delete->delete()
					->where(['ledger_id' => $supplier->ledger->id,'company_id'=>$company_id,'is_opening_balance'=>'yes'])
					->execute();
					
					$transaction_date=$this->Auth->User('session_company')->books_beginning_from;
					$AccountingEntry = $this->Suppliers->Ledgers->AccountingEntries->newEntity();
					$AccountingEntry->ledger_id        = $supplier->ledger->id;
					if($supplier->debit_credit=="Dr")
					{
						$AccountingEntry->debit        = $supplier->opening_balance_value;
					}
					if($supplier->debit_credit=="Cr")
					{
						$AccountingEntry->credit       = $supplier->opening_balance_value;
					}
					$AccountingEntry->customer_id      = $supplier->id;
					$AccountingEntry->transaction_date = date("Y-m-d",strtotime($transaction_date));
					$AccountingEntry->company_id       = $company_id;
					$AccountingEntry->is_opening_balance = 'yes';
					if($supplier->opening_balance_value){
					$this->Suppliers->Ledgers->AccountingEntries->save($AccountingEntry);
					}
                $this->Flash->success(__('The supplier has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The supplier could not be saved. Please, try again.'));
        }
		
		$SundryDebtor = $this->Suppliers->Ledgers->AccountingGroups->find()->where(['supplier'=>1,'company_id'=>$company_id])->first();
		$accountingGroups = $this->Suppliers->Ledgers->AccountingGroups
							->find('children', ['for' => $SundryDebtor->id])
							->find('List')->toArray();
		$accountingGroups[$SundryDebtor->id]=$SundryDebtor->name;
		ksort($accountingGroups);
		
		$account_entry  = $this->Suppliers->Ledgers->AccountingEntries->find()->where(['ledger_id'=>$supplier->ledger->id,'company_id'=>$company_id])->first();
		$states = $this->Suppliers->States->find('list',
													['keyField' => function ($row) {
														return $row['id'];
													},
													'valueField' => function ($row) 
													{
														if($row['state_code']<=9)
														{
															return str_pad($this->_properties['state_code'], 1, '0', STR_PAD_LEFT).$row['state_code'].'-'. $row['name'] ;
														}
														else
														{
															return $row['state_code'].'-'. $row['name'] ;
														}
													}]);
        $this->set(compact('supplier', 'states', 'accountingGroups','account_entry'));
        $this->set('_serialize', ['supplier']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Supplier id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
		
        $this->request->allowMethod(['post', 'delete']);
        $supplier = $this->Suppliers->get($id);
        if ($this->Suppliers->delete($supplier)) {
            $this->Flash->success(__('The supplier has been deleted.'));
        } else {
            $this->Flash->error(__('The supplier could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
