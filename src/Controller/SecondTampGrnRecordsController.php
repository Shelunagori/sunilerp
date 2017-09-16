<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\View\Helper\BarcodeHelper;

/**
 * SecondTampGrnRecords Controller
 *
 * @property \App\Model\Table\SecondTampGrnRecordsTable $SecondTampGrnRecords
 *
 * @method \App\Model\Entity\SecondTampGrnRecord[] paginate($object = null, array $settings = [])
 */
class SecondTampGrnRecordsController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index($invalid=null)
    {
		$this->viewBuilder()->layout('index_layout');
		$company_id=$this->Auth->User('session_company_id');
		$user_id=$this->Auth->User('id');
        $this->paginate = [
            'contain' => ['Units','FirstGstFigures','SecondGstFigures']
        ];
		if($invalid){
			$where=['SecondTampGrnRecords.company_id'=>$company_id,'SecondTampGrnRecords.user_id'=>$user_id, 'valid_to_import'=>'no'];
		}else{
			$where=['SecondTampGrnRecords.company_id'=>$company_id,'SecondTampGrnRecords.user_id'=>$user_id];
		}
        $secondTampGrnRecords = $this->paginate($this->SecondTampGrnRecords->find()->where($where));

        $this->set(compact('secondTampGrnRecords'));
        $this->set('_serialize', ['secondTampGrnRecords']);
    }

    /**
     * View method
     *
     * @param string|null $id Second Tamp Grn Record id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $secondTampGrnRecord = $this->SecondTampGrnRecords->get($id, [
            'contain' => ['Users']
        ]);

        $this->set('secondTampGrnRecord', $secondTampGrnRecord);
        $this->set('_serialize', ['secondTampGrnRecord']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $secondTampGrnRecord = $this->SecondTampGrnRecords->newEntity();
        if ($this->request->is('post')) {
            $secondTampGrnRecord = $this->SecondTampGrnRecords->patchEntity($secondTampGrnRecord, $this->request->getData());
            if ($this->SecondTampGrnRecords->save($secondTampGrnRecord)) {
                $this->Flash->success(__('The second tamp grn record has been saved.'));

                return $this->redirect(['action' => 'add']);
            }
            $this->Flash->error(__('The second tamp grn record could not be saved. Please, try again.'));
        }
        $users = $this->SecondTampGrnRecords->Users->find('list', ['limit' => 200]);
        $this->set(compact('secondTampGrnRecord', 'users'));
        $this->set('_serialize', ['secondTampGrnRecord']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Second Tamp Grn Record id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
		$this->viewBuilder()->layout('index_layout');
		$company_id=$this->Auth->User('session_company_id');
		$location_id=$this->Auth->User('session_location_id');
		$user_id=$this->Auth->User('id');
        $secondTampGrnRecord = $this->SecondTampGrnRecords->get($id, [
            'contain' => ['Units']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
			$secondTampGrnRecord->valid_to_import ='yes';
            $secondTampGrnRecord = $this->SecondTampGrnRecords->patchEntity($secondTampGrnRecord, $this->request->getData());
			
            if ($this->SecondTampGrnRecords->save($secondTampGrnRecord)) {
				$this->Flash->success(__('The second tamp grn record has been saved.'));
				$transaction_date=$this->Auth->User('session_company')->books_beginning_from;
				$item=$this->SecondTampGrnRecords->Companies->Items->newEntity();
				$item->name=$secondTampGrnRecord->item_name;
				$item->item_code=$secondTampGrnRecord->item_code;
				$item->hsn_code=$secondTampGrnRecord->hsn_code;
				$item->unit_id=$secondTampGrnRecord->unit_id;
				$item->company_id=$company_id;
				$item->first_gst_figure_id=$secondTampGrnRecord->first_gst_figure_id;
				$item->gst_amount=$secondTampGrnRecord->amount_in_ref_of_gst_rate;
				$item->second_gst_figure_id=$secondTampGrnRecord->second_gst_figure_id;
				$item->kind_of_gst=$secondTampGrnRecord->gst_rate_fixed_or_fluid;
				$item->sales_rate=$secondTampGrnRecord->sales_rate;
				$item->sales_rate_update_on=date("Y-m-d",strtotime($transaction_date));
				$item->location_id=$location_id;
				$item->item_code=strtoupper($secondTampGrnRecord->item_code);
				$data_to_encode = strtoupper($secondTampGrnRecord->item_code);
				$CheckItem = $this->SecondTampGrnRecords->Companies->Items->exists(['Items.item_code'=>$secondTampGrnRecord->item_code,'Items.company_id'=>$company_id]);
				if(!$CheckItem)
				{
					
					
					if($this->SecondTampGrnRecords->Companies->Items->save($item)){
					
						$barcode = new BarcodeHelper(new \Cake\View\View());
						// Generate Barcode data
						$barcode->barcode();
						$barcode->setType('C128');
						$barcode->setCode($data_to_encode);
						$barcode->setSize(40,100);
							
						// Generate filename     
						$file = 'img/barcode/'.$item->id.'.png';
							
						// Generates image file on server    
						$barcode->writeBarcodeFile($file);
					
						$query = $this->SecondTampGrnRecords->query();
						$query->update()
							->set(['item_id' => $item->id])
							->where(['SecondTampGrnRecords.id' =>$secondTampGrnRecord->id])
							->execute();
					}
				}
                return $this->redirect(['action' => 'import_step2','controller' =>'Grns']);
            }
            $this->Flash->error(__('The second tamp grn record could not be saved. Please, try again.'));
        }
		$units = $this->SecondTampGrnRecords->Units->find('list');
        $users = $this->SecondTampGrnRecords->Users->find('list');
		$gstFigures = $this->SecondTampGrnRecords->GstFigures->find('list')->where(['GstFigures.company_id'=>$company_id]);
        $this->set(compact('secondTampGrnRecord', 'users','units','gstFigures'));
        $this->set('_serialize', ['secondTampGrnRecord']);
    }
	
	
    /**
     * Delete method
     *
     * @param string|null $id Second Tamp Grn Record id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $secondTampGrnRecord = $this->SecondTampGrnRecords->get($id);
        if ($this->SecondTampGrnRecords->delete($secondTampGrnRecord)) {
            $this->Flash->success(__('The second tamp grn record has been deleted.'));
        } else {
            $this->Flash->error(__('The second tamp grn record could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
	
	public function finalImport(){
		$this->viewBuilder()->layout('index_layout');
		$company_id=$this->Auth->User('session_company_id');
        $location_id=$this->Auth->User('session_location_id');
		$this->request->data['company_id'] =$company_id;
		$grn = $this->SecondTampGrnRecords->Grns->newEntity();
		if ($this->request->is('post')) 
		{
			$grn = $this->SecondTampGrnRecords->Grns->patchEntity($grn, $this->request->getData());
			$grn->transaction_date = date("Y-m-d",strtotime($this->request->getData()['transaction_date']));
			$Voucher_no = $this->SecondTampGrnRecords->Grns->find()->select(['voucher_no'])->where(['company_id'=>$company_id])->order(['voucher_no' => 'DESC'])->first();
			if($Voucher_no)
			{
				$grn->voucher_no = $Voucher_no->voucher_no+1;
			}
			else
			{
				$grn->voucher_no = 1;
			} 
			$grn->location_id=$location_id;
            if ($this->SecondTampGrnRecords->Grns->save($grn)) 
			{
				return $this->redirect(['action' => 'progress1/'.$grn->id]);
            }
		}
		$Voucher_no = $this->SecondTampGrnRecords->Grns->find()->select(['voucher_no'])->where(['company_id'=>$company_id])->order(['voucher_no' => 'DESC'])->first();
		
		if($Voucher_no)
		{
			$voucher_no=$Voucher_no->voucher_no+1;
		}
		else
		{ 
			$voucher_no=1;
			
		} 
		$this->set(compact('SecondTampGrnRecords','grn','voucher_no'));
        $this->set('_serialize', ['SecondTampGrnRecords']);
	}

	public function progress()
	{
		$this->viewBuilder()->layout('index_layout');
		$SecondTampGrnRecords = $this->SecondTampGrnRecords->newEntity();
		$this->set(compact('SecondTampGrnRecords'));
        $this->set('_serialize', ['SecondTampGrnRecords']);
	}
	
	public function progress1($id=null)
	{
		$this->viewBuilder()->layout('index_layout');
		$SecondTampGrnRecords = $this->SecondTampGrnRecords->newEntity();
		$grn_id=$id;
		$this->set(compact('SecondTampGrnRecords','grn_id'));
        $this->set('_serialize', ['SecondTampGrnRecords']);
	}

	public function ProcessDataImport($grn_id=null)
	{
		$user_id=$this->Auth->User('id');
		$company_id=$this->Auth->User('session_company_id');
		$location_id=$this->Auth->User('session_location_id');
		$SecondTampGrnRecords = $this->SecondTampGrnRecords->find()
								->where(['user_id'=>$user_id,'company_id'=>$company_id,'import_to_grn'=>'no'])
								->limit(5);
		
		if($SecondTampGrnRecords->count()==0){
			goto Bottom;
		}
		foreach($SecondTampGrnRecords as $SecondTampGrnRecord){
			$GrnRows = $this->SecondTampGrnRecords->Grns->GrnRows->newEntity();;
				$GrnRows->grn_id = $grn_id;
				$GrnRows->item_id = $SecondTampGrnRecord->item_id;
				$GrnRows->quantity = $SecondTampGrnRecord->quantity;
				$GrnRows->purchase_rate = $SecondTampGrnRecord->purchase_rate;
				$GrnRows->sale_rate = $SecondTampGrnRecord->sales_rate;
				if($this->SecondTampGrnRecords->Grns->GrnRows->save($GrnRows)){
					$query = $this->SecondTampGrnRecords->query();
					$query->update()
					->set(['import_to_grn' => 'yes'])
					->where(['SecondTampGrnRecords.id' =>$SecondTampGrnRecord->id])
					->execute();
				}
		}
		$grn_rows=$this->SecondTampGrnRecords->Grns->GrnRows->find()
								->where(['GrnRows.grn_id'=>$grn_id,'import_to_itemledger' => 'no']);
		
		
		$grn=$this->SecondTampGrnRecords->Grns->find()
								->where(['company_id'=>$company_id,'Grns.id'=>$grn_id])->first();
			
		foreach($grn_rows as $grn_row)
		{
				$item = $this->SecondTampGrnRecords->Grns->GrnRows->Items->find()->where(['Items.id'=>$grn_row->item_id])->first();
					
					if($grn->transaction_date <= date("Y-m-d",strtotime($item->sales_rate_update_on)))
					{
						$query = $this->SecondTampGrnRecords->Grns->GrnRows->Items->query();
						$query->update()
								->set(['sales_rate' => $grn_row->sale_rate])
								->where(['Items.id' =>$grn_row->item_id])
								->execute();
			        }
				$item_ledger = $this->SecondTampGrnRecords->Grns->ItemLedgers->newEntity();
					$item_ledger->transaction_date = $grn->transaction_date;
					$item_ledger->grn_id = $grn_id;
					$item_ledger->grn_row_id = $grn_row->id;
					$item_ledger->item_id = $grn_row->item_id;
					$item_ledger->quantity = $grn_row->quantity;
					$item_ledger->rate = $grn_row->purchase_rate;
					$item_ledger->sale_rate = $grn_row->sale_rate;
					$item_ledger->company_id =$company_id;
					$item_ledger->status ='in';
					$item_ledger->amount=$grn_row->quantity*$grn_row->purchase_rate;
					$item_ledger->location_id=$location_id;
					$this->SecondTampGrnRecords->Grns->ItemLedgers->save($item_ledger);
					if($this->SecondTampGrnRecords->Grns->ItemLedgers->save($item_ledger)){
						$query = $this->SecondTampGrnRecords->Grns->GrnRows->query();
						$query->update()
						->set(['import_to_itemledger' => 'yes'])
						->where(['GrnRows.id' =>$grn_row->id])
						->execute();
					}
		}
		
		Bottom:
		$totalRecords=$this->SecondTampGrnRecords->find()
						->where(['user_id'=>$user_id,'company_id'=>$company_id])
						->count();
		$processedRecords=$this->SecondTampGrnRecords->find()
						->where(['user_id'=>$user_id,'company_id'=>$company_id,'import_to_grn'=>'yes'])
						->count();
		$progress_percentage = round((($processedRecords*100)/$totalRecords),2);
		$data['percantage'] = $progress_percentage;

		if($totalRecords==$processedRecords){ 
			$data['recallAjax'] = "false";
		}else{
			$data['recallAjax'] = "true";
		}
		echo json_encode($data);
		exit;
	}

	
	public function ProcessData()
	{
		$user_id=$this->Auth->User('id');
		$company_id=$this->Auth->User('session_company_id');
		$location_id=$this->Auth->User('session_location_id');
		$SecondTampGrnRecords = $this->SecondTampGrnRecords->find()
								->where(['user_id'=>$user_id,'company_id'=>$company_id,'processed'=>'no'])
								->limit(1);
		if($SecondTampGrnRecords->count()==0){
			goto Bottom;
		}
		foreach($SecondTampGrnRecords as $SecondTampGrnRecord){
			
			$item=$this->SecondTampGrnRecords->Companies->Items->find()
					->where(['Items.item_code'=>$SecondTampGrnRecord->item_code,'company_id'=>$company_id])->first();
			if(!$item){
				if(empty($SecondTampGrnRecord->hsn_code)){
					goto DoNotMarkYesValidToImport;
				}
				
				if(empty($SecondTampGrnRecord->provided_unit)){
					goto DoNotMarkYesValidToImport;
				}
			
				if(empty($SecondTampGrnRecord->item_name)){
					goto DoNotMarkYesValidToImport;
				}
				
				$unit=$this->SecondTampGrnRecords->Companies->Items->Units->find()
						->where(['Units.name LIKE'=>'%'.trim($SecondTampGrnRecord->provided_unit).'%', 'Units.company_id'=>$company_id])
						->first();
				if($unit){
					$query = $this->SecondTampGrnRecords->query();
					$query->update()
						->set(['unit_id' => $unit->id])
						->where(['SecondTampGrnRecords.id' =>$SecondTampGrnRecord->id])
						->execute();
					
				}else{
					goto DoNotMarkYesValidToImport;
				}
				
				$a=['fix','fluid'];
				
				if (in_array(strtolower($SecondTampGrnRecord->gst_rate_fixed_or_fluid), $a)){
					
					$key = array_search(strtolower($SecondTampGrnRecord->gst_rate_fixed_or_fluid), $a);
					
					if($key=='0'){
						
						$gstFigure=$this->SecondTampGrnRecords->Companies->GstFigures->find()
									->where(['GstFigures.tax_percentage'=>floatval($SecondTampGrnRecord->first_gst_rate), 'GstFigures.company_id'=>$company_id])
									->first();
						if($gstFigure){
							$query = $this->SecondTampGrnRecords->query();
							$query->update()
								->set(['first_gst_figure_id' => $gstFigure->id])
								->where(['SecondTampGrnRecords.id' =>$SecondTampGrnRecord->id])
								->execute();
							$first_gst_figure_id=$gstFigure->id;
							
							$query = $this->SecondTampGrnRecords->query();
							$query->update()
								->set(['amount_in_ref_of_gst_rate' => 0])
								->where(['SecondTampGrnRecords.id' =>$SecondTampGrnRecord->id])
								->execute();
								
							$query = $this->SecondTampGrnRecords->query();
							$query->update()
								->set(['second_gst_figure_id' => $gstFigure->id])
								->where(['SecondTampGrnRecords.id' =>$SecondTampGrnRecord->id])
								->execute();
							$second_gst_figure_id=$gstFigure->id;
						}else{
							goto DoNotMarkYesValidToImport;
						}
						
					}else{
						$gstFigure=$this->SecondTampGrnRecords->Companies->GstFigures->find()
									->where(['GstFigures.tax_percentage'=>floatval($SecondTampGrnRecord->first_gst_rate), 'GstFigures.company_id'=>$company_id])
									->first();
						if($gstFigure){
							$query = $this->SecondTampGrnRecords->query();
							$query->update()
								->set(['first_gst_figure_id' => $gstFigure->id])
								->where(['SecondTampGrnRecords.id' =>$SecondTampGrnRecord->id])
								->execute();
							
							$first_gst_figure_id=$gstFigure->id;
						}else{
							goto DoNotMarkYesValidToImport;
						}
						
						$secondgstFigure=$this->SecondTampGrnRecords->Companies->GstFigures->find()
									->where(['GstFigures.tax_percentage'=>floatval($SecondTampGrnRecord->second_gst_rate), 'GstFigures.company_id'=>$company_id])
									->first();
						if($secondgstFigure){
							$query = $this->SecondTampGrnRecords->query();
							$query->update()
								->set(['second_gst_figure_id' => $secondgstFigure->id])
								->where(['SecondTampGrnRecords.id' =>$SecondTampGrnRecord->id])
								->execute();
							
							$second_gst_figure_id=$gstFigure->id;
						}else{
							goto DoNotMarkYesValidToImport;
						}
					}
				}else{
					goto DoNotMarkYesValidToImport;
				}
				
				
				
				//Item Creation
				
				$transaction_date=$this->Auth->User('session_company')->books_beginning_from;
				
				$item=$this->SecondTampGrnRecords->Companies->Items->newEntity();
				$item->name=$SecondTampGrnRecord->item_name;
				if(!empty($SecondTampGrnRecord->item_code)){
					$item->item_code=$SecondTampGrnRecord->item_code;
					$data_to_encode = strtoupper($SecondTampGrnRecord->item_code);
				}else{
					$item->item_code=strtoupper(uniqid());
					$data_to_encode = strtoupper(uniqid());
				}
				
				$item->hsn_code=$SecondTampGrnRecord->hsn_code;
				$item->unit_id=$unit->id;
				$item->company_id=$company_id;
				$item->first_gst_figure_id=$first_gst_figure_id;
				$item->gst_amount=$SecondTampGrnRecord->amount_in_ref_of_gst_rate;
				$item->second_gst_figure_id=$second_gst_figure_id;
				$item->kind_of_gst=$SecondTampGrnRecord->gst_rate_fixed_or_fluid;
				$item->purchase_rate=$SecondTampGrnRecord->purchase_rate;
				$item->sales_rate=$SecondTampGrnRecord->sales_rate;
				$item->sales_rate_update_on=date("Y-m-d",strtotime($transaction_date));
				$item->location_id=$location_id;
				//$item->item_code=strtoupper($SecondTampGrnRecord->item_code);
				
				if($this->SecondTampGrnRecords->Companies->Items->save($item)){
					
					$barcode = new BarcodeHelper(new \Cake\View\View());
					// Generate Barcode data
					$barcode->barcode();
					$barcode->setType('C128');
					$barcode->setCode($data_to_encode);
					$barcode->setSize(40,100);
						
					// Generate filename     
					$file = 'img/barcode/'.$item->id.'.png';
						
					// Generates image file on server    
					$barcode->writeBarcodeFile($file);
				
					$query = $this->SecondTampGrnRecords->query();
					$query->update()
						->set(['item_id' => $item->id])
						->where(['SecondTampGrnRecords.id' =>$SecondTampGrnRecord->id])
						->execute();
				}else{
					goto DoNotMarkYesValidToImport;
				}
			}else{
				$query = $this->SecondTampGrnRecords->query();
				$query->update()
					->set(['item_id' => $item->id])
					->where(['SecondTampGrnRecords.id' =>$SecondTampGrnRecord->id])
					->execute();
			}
			
			$query = $this->SecondTampGrnRecords->query();
			$query->update()
					->set(['valid_to_import' => 'yes'])
					->where(['SecondTampGrnRecords.id' =>$SecondTampGrnRecord->id])
					->execute();
			//DoNotMarkYesValidToImport
			DoNotMarkYesValidToImport:
			
			$query = $this->SecondTampGrnRecords->query();
			$query->update()
					->set(['processed' => 'yes'])
					->where(['SecondTampGrnRecords.id' =>$SecondTampGrnRecord->id])
					->execute();
		}
		Bottom:
		$totalRecords=$this->SecondTampGrnRecords->find()
						->where(['user_id'=>$user_id,'company_id'=>$company_id])
						->count();
		$processedRecords=$this->SecondTampGrnRecords->find()
						->where(['user_id'=>$user_id,'company_id'=>$company_id,'processed'=>'yes'])
						->count();
		$progress_percentage = round((($processedRecords*100)/$totalRecords),2);
		$data['percantage'] = $progress_percentage;

		if($totalRecords==$processedRecords){ 
			$data['recallAjax'] = "false";
			 
		}else{
			$data['recallAjax'] = "true";
			
		}
		echo json_encode($data);
		exit;
	}
	
	public function deleteSecondTempRecords(){
		$user_id=$this->Auth->User('id');
		$company_id=$this->Auth->User('session_company_id');
		$location_id=$this->Auth->User('session_location_id');
		
		$query = $this->SecondTampGrnRecords->query();
				$query->delete()->where(['user_id'=> $user_id,'company_id'=>$company_id])->execute();
				
		return $this->redirect(['action' => 'import_csv','controller' =>'Grns']);
           
		
	}
	
}

