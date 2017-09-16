<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Customers Controller
 *
 * @property \App\Model\Table\CustomersTable $Customers
 *
 * @method \App\Model\Entity\Customer[] paginate($object = null, array $settings = [])
 */
class CustomersController extends AppController
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
        $customers = $this->paginate($this->Customers->find());

        $this->set(compact('customers'));
        $this->set('_serialize', ['customers']);
    }

    /**
     * View method
     *
     * @param string|null $id Customer id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $customer = $this->Customers->get($id, [
            'contain' => ['States', 'Ledgers']
        ]);

        $this->set('customer', $customer);
        $this->set('_serialize', ['customer']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
		$this->viewBuilder()->layout('index_layout');
		$company_id=$this->Auth->User('session_company_id');
        $customer = $this->Customers->newEntity();
		$this->request->data['company_id'] = $company_id;
        if ($this->request->is('post')) {
			
			$customer = $this->Customers->patchEntity($customer, $this->request->data);
			
			if ($this->Customers->save($customer)) {
				
				//Create Ledger//
				$ledger = $this->Customers->Ledgers->newEntity();
				$ledger->name = $customer->name;
				$ledger->accounting_group_id = $customer->accounting_group_id;
				$ledger->company_id =$company_id;
				$ledger->customer_id=$customer->id;
				$ledger->bill_to_bill_accounting='no';//$customer->bill_to_bill_accounting;
				
				if($this->Customers->Ledgers->save($ledger))
				{
					//Create Accounting Entry//
			        $transaction_date=$this->Auth->User('session_company')->books_beginning_from;
					$AccountingEntry = $this->Customers->Ledgers->AccountingEntries->newEntity();
					$AccountingEntry->ledger_id = $ledger->id;
					if($customer->debit_credit=="Dr")
					{
						$AccountingEntry->debit = $customer->opening_balance_value;
					}
					if($customer->debit_credit=="Cr")
					{
						$AccountingEntry->credit = $customer->opening_balance_value;
					}
					$AccountingEntry->customer_id      = $customer->id;
					$AccountingEntry->transaction_date = date("Y-m-d",strtotime($transaction_date));
					$AccountingEntry->company_id       = $company_id;
					$AccountingEntry->is_opening_balance = 'yes';
					if($customer->opening_balance_value){
					$this->Customers->Ledgers->AccountingEntries->save($AccountingEntry);
					}
					
				}

                $this->Flash->success(__('The customer has been saved.'));

                return $this->redirect(['action' => 'add']);
            }
			$this->Flash->error(__('The customer could not be saved. Please, try again.'));
        }
		$SundryDebtor = $this->Customers->Ledgers->AccountingGroups->find()->where(['customer'=>1,'company_id'=>$company_id])->first();
		$accountingGroups = $this->Customers->Ledgers->AccountingGroups
							->find('children', ['for' => $SundryDebtor->id])
							->find('List')->toArray();
		$accountingGroups[$SundryDebtor->id]=$SundryDebtor->name;
		ksort($accountingGroups);
        $states = $this->Customers->States->  find('list',
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
		
        $this->set(compact('customer', 'states','accountingGroups'));
        $this->set('_serialize', ['customer', 'accountingGroups']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Customer id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
		$this->viewBuilder()->layout('index_layout');
        $customer = $this->Customers->get($id, [
            'contain' => ['Ledgers']
        ]);
		
		$company_id=$this->Auth->User('session_company_id');
        if ($this->request->is(['patch', 'post', 'put'])) {
            $customer = $this->Customers->patchEntity($customer, $this->request->getData());
            if ($this->Customers->save($customer)) {
				$query = $this->Customers->Ledgers->query();
					$query->update()
						->set(['name' => $customer->name,'accounting_group_id'=>$customer->accounting_group_id])
						->where(['customer_id' => $id,'company_id'=>$company_id,'bill_to_bill_accounting'=>'yes'])
						->execute();
						
					//Accounting Entry
					$query_delete = $this->Customers->Ledgers->AccountingEntries->query();
					$query_delete->delete()
					->where(['ledger_id' => $customer->ledger->id,'company_id'=>$company_id,'is_opening_balance'=>'yes'])
					->execute();
					
					$transaction_date=$this->Auth->User('session_company')->books_beginning_from;
					$AccountingEntry = $this->Customers->Ledgers->AccountingEntries->newEntity();
					$AccountingEntry->ledger_id        = $customer->ledger->id;
					if($customer->debit_credit=="Dr")
					{
						$AccountingEntry->debit        = $customer->opening_balance_value;
					}
					if($customer->debit_credit=="Cr")
					{
						$AccountingEntry->credit       = $customer->opening_balance_value;
					}
					$AccountingEntry->customer_id      = $customer->id;
					$AccountingEntry->transaction_date = date("Y-m-d",strtotime($transaction_date));
					$AccountingEntry->company_id       = $company_id;
					$AccountingEntry->is_opening_balance = 'yes';
					if($customer->opening_balance_value){
					$this->Customers->Ledgers->AccountingEntries->save($AccountingEntry);
					}
                $this->Flash->success(__('The customer has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The customer could not be saved. Please, try again.'));
        }
		
		$SundryDebtor = $this->Customers->Ledgers->AccountingGroups->find()->where(['customer'=>1,'company_id'=>$company_id])->first();
		$accountingGroups = $this->Customers->Ledgers->AccountingGroups
							->find('children', ['for' => $SundryDebtor->id])
							->find('List')->toArray();
		$accountingGroups[$SundryDebtor->id]=$SundryDebtor->name;
		ksort($accountingGroups);
		$account_entry  = $this->Customers->Ledgers->AccountingEntries->find()->where(['ledger_id'=>$customer->ledger->id,'company_id'=>$company_id])->first();
		//pr($account_entry->toArray());exit;
        $states = $this->Customers->States->find('list',
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
		$this->set(compact('customer', 'states','accountingGroups','account_entry'));
        $this->set('_serialize', ['customer']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Customer id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
		
        $this->request->allowMethod(['post', 'delete']);
        $customer = $this->Customers->get($id);
        if ($this->Customers->delete($customer)) {
            $this->Flash->success(__('The customer has been deleted.'));
        } else {
            $this->Flash->error(__('The customer could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
