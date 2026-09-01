@extends('backend.partials.master')
@section('title', 'Customer Booking')

@section('maincontent')
<main class="content">
  <div class="container-fluid p-0">
    <div class="mb-3">
      <h1 class="h3 d-inline align-middle">Customer Booking</h1>
    </div>
<style>
.pdtl{
	display:none;
}</style>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <!-- @if ($errors->any())
              <div class="alert alert-danger">
                  <ul class="mb-0">
                      @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                      @endforeach
                  </ul>
              </div>
          @endif -->

        @php
        $PayStyle=$PStyle=$Style="display: none;";

        @endphp
          <div class="card-body">
          <form action="@if(!empty($postdated)){{ route('customer_booking.update') }}@else{{ route('customer_booking.store') }}@endif"
                  method="POST" enctype="multipart/form-data">
                  
              @csrf
              
              @if(!empty($postdated))
                @method('PUT')
                <input type="hidden" name="ID" value="{{ $postdated->ID }}">
              @endif
              {{--<input type="hidden" name="purchasesid" value="{{ $purchases->id }}">--}}

              <div class="row">
                <div class="mb-3 col-md-3">
                    <label class="form-label" for="name">Customer Name <small class="text-danger">*</small></label>
                    <input type="text" class="form-control "  name="name" id="name" value="{{ old('name', $postdated->CutomerName ?? '') }}" required>
                    @error('date')
                      <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

   
                <div class="mb-3 col-md-3">
                  <label class="form-label" for="name">Address <small class="text-danger">*</small></label>
                  <textarea id="address" name="address"  class="form-control" required>{{ old('address', $postdated->Address ?? '') }}</textarea>
                </div>

              
                  <div class="mb-3 col-md-3">
                    <label class="form-label" for="name">Village</label>
                    <input name="village" id="village"  type="text" class="form-control " value="{{ old('village', $postdated->village ?? '') }}"/>
                  </div>

                  <div class="mb-3 col-md-3">
                    <label class="form-label" for="name">Taluka</label>
				              <input name="taluka" id="taluka" type="text" class="form-control " value="{{ old('village', $postdated->taluka ?? '') }}"/>
				          </div>

                    <div class="mb-3 col-md-3">
                      <label class="form-label" for="name">District</label>
                      <input name="district" id="district"   type="text" class="form-control " value="{{ old('district', $postdated->district ?? '') }}"/>
                    </div>

                  <div class="mb-3 col-md-3">
                    <label class="form-label" for="name">Pincode</label>
				            <input name="pincode" id="pincode" type="text"  class="form-control " value="{{ old('pincode', $postdated->pincode ?? '') }}"/>
				          </div>
  
                  <div class="mb-3 col-md-3">
                     <label class="form-label" for="name">Mobile / Phone</label>
				              <input name="contactno" id="contactno"   type="text" class="form-control mobile" value="{{ old('contactno', $postdated->Contact ?? '') }}"/>
				          </div>

                  
                  <div class="col-md-3">
                    <label class="form-label" for="email">Email</label>
				            <input name="email" id="email" type="email"  class="form-control email mb-2" value="{{ old('email', $postdated->Email ?? '') }}"/>
				          </div>

                  <div class="mb-3 col-md-3">
                    <label class="form-label" for="date">Booking Date <small class="text-danger">*</small></label>
                    <input type="date" class="form-control @error('date') is-invalid @enderror"  name="bdate" id="bdate" value="{{ old('bdate', $postdated->BookingDate ?? date('Y-m-d')) }}" required>
                    @error('date')
                      <small class="text-danger">{{ $message }}</small>
                    @enderror
                  </div>

                <div class="mb-3 col-md-3">
                  <label for="scheme" class="form-label">Scheme <small class="text-danger">*</small></label>
                  <select name="scheme" id="scheme" class="form-control choices-single" required >
                    <option disabled selected value="">Select Scheme</option>
                   
                    @foreach($schemes as $scheme)
                      <option value="{{ $scheme->ID }}"
                       {{ (session('selected_scheme_id') == $scheme->ID) ? 'selected' : '' }}>
                          
                        {{ $scheme->Name }}
                        
                      </option>
                    
                    @endforeach
                  </select>
                  @error('scheme')
                    <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
                <div class="mb-3 col-md-3">
                  <label for="scheme" class="form-label">ID Proof <small class="text-danger">*</small></label>
                  <select name="idproof" id="idproof" class="form-control" required>
                      <option value="">Select ID Proof</option>
                      <option value="Voter ID" {{ old('Idproof', $postdated->Idproof ?? '') == 'Voter ID' ? 'selected' : '' }}>Voter ID</option>
                      <option value="PAN Card" {{ old('Idproof', $postdated->Idproof ?? '') == 'PAN Card' ? 'selected' : '' }}>PAN Card</option>
                      <option value="Adhar Card" {{ old('Idproof', $postdated->Idproof ?? '') == 'Adhar Card' ? 'selected' : '' }}>Adhar Card</option>
                      <option value="License" {{ old('Idproof', $postdated->Idproof ?? '') == 'License' ? 'selected' : '' }}>License</option>
                  </select>
                        @error('Idproof')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                </div>


                <div class="mb-3 col-md-3">
                        <label class="form-label">Upload</label><br>

                        @if(!empty($postdated) && $postdated->scanimg)
                            <img src="{{ asset($postdated->scanimg) }}" height="80" alt="Dish Image"><br>
                        @endif

                        <input type="file" name="scanimg" class="form-control">
                        @error('scanimg')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                 
                  	<div class="mb-3 col-md-3">
                        <label class="form-label">Agreement complete</label><br>
                         
                        <input type="radio" name="agreement_complete" value="1"
                             {{ old('agreement_complete', $postdated->agreement_complete ?? '') == '1' ? 'checked' : '' }}
                            onclick="toggleAgreement(true)"> Yes &nbsp;

                        <input type="radio" name="agreement_complete" value="0"
                           {{ old('agreement_complete', $postdated->agreement_complete ?? '') == '0' ? 'checked' : '' }}
                            onclick="toggleAgreement(false)"> No
                    </div>

                    <!-- Agreement Details -->
                    <div id="aggrement_div" style="display:none;">
                        <div class="row">
                            <div class="mb-3 col-md-3">
                                <label class="form-label">Agreement Number</label>
                                <input name="agreement_no" id="agreement_no" type="text"
                                      class="form-control"
                                      value="{{ old('agreement_no', $postdated->agreement_no ?? '') }}">
                            </div>

                            <div class="mb-3 col-md-3">
                                <label class="form-label">Registration Date</label>
                                <input name="reg_date" id="reg_date" type="text"
                                      class="form-control"
                                      value="{{ old('reg_date', $postdated->reg_date ?? date('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>
  
                    
              </div><br>
              
              <h4>Flat Details</h4>
              <hr>

              <div class="row">

                 <div class="mb-3 col-md-3" id="Type_div">
                      <label for="wing" class="form-label">Wing <small class="text-danger">*</small></label>
                         <select name="typesrch" id="typesrch" class="input-sm form-control chosen-select required" data-placeholder="Select" onchange="wing();">
                          <option value="">Select Wing</option>
                          @php
                              if(!empty($postdated)){

                                  $type_details = \DB::table('flats_details')
                                                  ->select('Wing')
                                                  ->where('scheme_ID', $postdated->Scheme)
                                                  ->groupBy('Wing')
                                                  ->get();

                              }else{
                                    $type_details = \DB::table('flats_details')
                                                  ->select('Wing')
                                                  ->where('scheme_ID', $clientId)
                                                  ->groupBy('Wing')
                                                  ->get();
                                   
                              }
                            
                          @endphp

                          @foreach($type_details as $type_rec)
                              <option value="{{ $type_rec->Wing }}" 
                                  {{ old('typesrch', $postdated->Wing ?? '') == $type_rec->Wing ? 'selected' : '' }}>
                              {{ $type_rec->Wing }}
                              </option>
                          @endforeach
                      </select>

                      <input type="hidden" id="schemeH" name="schemeH" value="{{ $clientId ?? 0 }}">
                  </div>
                   <div class="mb-3 col-md-3" >
                     <label for="flat_no" class="form-label">Flat No <small class="text-danger">*</small></label>
                     <div id="div_fno">
                      <select name="fno" id="fno" class="input-sm form-control chosen-select required" data-placeholder="Select" onchange="getFlatInfo();">
                          <option>Select Flat No</option>    
                        @foreach($flatsDetails as $flat)
                            <option value="{{ $flat->ID }}"
                               {{ old('fno', $postdated->FlatNo ?? '') == $flat->ID ? 'selected' : '' }}>
                                 
                                {{ $flat->FlatNo }}
                            </option>
                        @endforeach

                    </select>

                    </div>
                  </div>
                   <div class="mb-3 col-md-3" >
                     <label for="flat_no" class="form-label">Flat Category </label>
                     <div id="fcatdiv">
                      @if(!empty($postdated))
                     {{ $flat_detail->first()->FlatType ?? '' }}

                    @endif
                    </div>
                  </div>
                  

              </div><br>

          <div class="row col-md-12"  style="margin-left: 0px;">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" style="height: 100px;width:90%">
                <thead>
                  <tr style="height: 50px;">
                  
                    <th>Wing</th>
                    <th>Floor</th>
                    <th>C.Area </th>
                    
                    <th>Balcony</th>
                    <th>En.Balcony</th>
                    <th>Terrace </th>
                    <th>P.C.Area</th>
                    <th>Total Sq Ft</th>
                    
                  </tr>
                </thead>
                <tbody>
                  <tr style="height: 50px">
                    <td>  <div class="col-md-4" id="wing"> @if(!empty($postdated)) {{ $flat_detail->first()->Wing ?? '' }} @endif</div></td>
                    <td> <div class="col-md-4" id="floor">@if(!empty($postdated)) {{ $flat_detail->first()->Floor ?? '' }} @endif</div></td>
                    <td> <div class="col-md-4" id="area"> @if(!empty($postdated)) {{ $flat_detail->first()->Area ?? '' }} @endif</div></td>
                    
                    <td> <div class="col-md-4" id="Other1">@if(!empty($postdated)) {{ $flat_detail->first()->Other1 ?? '' }} @endif</div></td>
                    <td> <div class="col-md-4" id="Other2">@if(!empty($postdated)) {{ $flat_detail->first()->Other2 ?? '' }} @endif</div></td>
                    <td><div class="col-md-4" id="TerraceArea">@if(!empty($postdated)) {{ $flat_detail->first()->Terrace ?? '' }} @endif</div></td>
                    <td><div class="col-md-4" id="Attribute"> @if(!empty($postdated)) {{ $flat_detail->first()->FlatAttribute ?? '' }} @endif</div>
                    <td><div class="col-md-4" id="totalSqFt">@if(!empty($postdated)) {{ $flat_detail->first()->TotalSqFt ?? '' }} @endif</div></td>
                    
                    <input name="farea" id="farea" type="hidden"  value=" <?php //echo $Flatdetails_rec['TotalSqFt']; ?>" onkeyup="calccost();" onblur="calccost();"/></td>
                  </tr>
                </tbody>
                </table>
</div>
              </div><br>

              <div class="row" >
                <div class="mb-3 col-md-3">
				 
				          <label>Rate Per Sq/ft:</label>
                              
			            <input name="rateamt" id="rateamt" value="1" {{ old('rateamt', $postdated->Ptype ?? '') == '1' ? 'checked' : '' }} type="radio"  onclick="show_ratesqft(this.value);" onblur=""/>
				        </div>
				        <div class="mb-3 col-md-3">
                  <label> Lump Sum Amount: </label>
               
					        <input name="rateamt" id="rateamt" type="radio"  onclick="show_ratesqft(this.value);" value="0" {{ old('rateamt', $postdated->Ptype ?? '') == '0' ? 'checked' : '' }} />
				        </div>
				        <div class="mb-3 col-md-3">
                  <label> Land Owner:</label>
               
					        <input name="rateamt" id="rateamt" type="radio"  onclick="show_ratesqft(this.value);" value="2" {{ old('rateamt', $postdated->Ptype ?? '') == '2' ? 'checked' : '' }} />
				        </div>
				      </div>
               <br>
               <?php 
               if(!empty($postdated))
               {
                   if($postdated->Ptype=='1' || $postdated->Ptype=='2'){$disp_box = "";}else{$disp_box = "none";} 
               } 
               else{
                   $disp_box = "none";
               }
              
               
               ?>

              <div id="show_rate" style="display:<?php echo $disp_box;?>;">

                  <div class="row">

                      <div class="mb-3 col-md-3" >
                         <label for="flat_no" class="form-label">Rate Per Sq/ft</label>
                        <input name="RateSqft" id="RateSqft" type="text" class="form-control number" value="{{ old('RateSqft', $postdated->RateSqft ?? '') }}" onkeyup="calccost();" onblur="calccost();"/>
                      </div> 

                      <div class="mb-3 col-md-3">
                          <label class="form-label">BSP Amount</label>
					                <input name="costBSP" id="costBSP" type="text" readonly class="form-control" value="{{ old('costBSP', $postdated->BspAmount ?? '') }}"/>
				              </div>

                      <div class="mb-3 col-md-3">
                        <label class="form-label">Agreement Amt</label>
					              <input name="cost" id="cost" type="text"  class="form-control" value="{{ old('cost', $postdated->agreementAmt ?? '') }}" onchange="diffrence();" onkeyup="diffrence();" onblur="diffrence();"/>
				              </div>
				  
                      <div class="mb-3 col-md-3">
                        <label class="form-label">Difference Amt</label>
					                <input name="diff" id="diff" type="text"  class="form-control" value="{{ old('diff', ($postdated['agreementAmt'] ?? 0) - ($postdated['BspAmount'] ?? 0)) }}"/>
				              </div>

                  </div><br>
                   <div class="row">
                      <h4 style="margin-left: 0px;"> Other Charges</h4>
                      <hr><br>
                     
                      <div class="mb-3 col-md-3" >
                        <label class="form-label">Light & water charges</label>
                        <input name="light" id="light" type="text" class="form-control number" value="{{ old('light', $postdated->lightChrg ?? '') }}" onkeyup="calOtherChrg();" onblur="calOtherChrg();"/> 
                      </div>

                      
                      <div class="mb-3 col-md-3" >
                        <label class="form-label">Parking charges</label>
                        <input name="parking" id="parking"  type="text" class="form-control number" value="{{ old('parking', $postdated->parkingChrg ?? '') }}" onkeyup="calOtherChrg();" onblur="calOtherChrg();"  /> 
                      </div>

                       
                      <div class="mb-3 col-md-3" id="floor">
                        <label class="form-label">Maintainace</label>
                        <input name="maintance"  id="maintance" type="text" class="form-control number" value="{{ old('maintance', $postdated->maintances ?? '') }}" onkeyup="calOtherChrg();" onblur="calOtherChrg();"  /> 
                      </div>

                      <div class="mb-3 col-md-3">
                        <label class="form-label">Legal Fees</label>
                        <input name="documentation" id="documentation"  type="text" class="form-control number"  value="{{ old('documentation', $postdated->docChrg ?? '') }}" onkeyup="calOtherChrg();" onblur="calOtherChrg();" />  
                      </div>
	  
                      <div class="mb-3 col-md-3">
                        <label class="form-label">Site Development Charges</label>      
                        <input name="sitedevcharges" id="sitedevcharges"  type="text" class="form-control number"  value="{{ old('sitedevcharges', $postdated->sitedevCharges ?? '') }}" onkeyup="calOtherChrg();" onblur="calOtherChrg();"/>  
                      </div>

				              
                      <div class="mb-3 col-md-3" id="wing">
                        <label class="form-label">Total Charges</label>
                        <input name="totalOthers"  id="totalOthers" type="text" class="form-control number" value="{{ old('totalOthers', $postdated->othersTotal ?? '') }}"   readonly /> 
                      </div>
                  </div><br>

                <div class="row" >
                  <h4 style="margin-left: 0px;">  Tax charges</h4>
			            <hr><br>

                    <div class="col-md-2 ">Agreement Amt</div>  
                  <div class="col-md-3">
                           
                      <input name="stamp_amt" id="stamp_amt" type="text" class="form-control number"  value="{{ old('stamp_amt', $postdated->stamp_amt ?? '') }}" onkeyup="totalTaxAmt();" onblur="totalTaxAmt();"/> 
                  </div>
                  
		            </div><br>
                <div class="row">

                  <div class="col-md-2 "  style="" >Stamp Duty: </div>
				            <div class="col-md-3 row ">
				              <div class="col-md-12 input-group">

				                <input name="vat_per" id="vat_per"  style="width: 50px;"  type="text" class="form-control number " value="{{ old('vat_per', $postdated->vatAmtPer ?? '') }}" onkeyup="totalTaxAmt();" onblur="totalTaxAmt();" />&nbsp;
				                <span class="" style=""><b>%</b></span>
				 
					              <input name="vat" id="vat"  readonly type="text" class="form-control " style="margin-left:42px;width: 90px;" value="{{ old('vat', $postdated->vatAmt ?? '') }}" onkeyup="totalTaxAmt();" onblur="totalTaxAmt();"/> &nbsp;
					              <span class="" style="margin-left:2px;"><b> &#8377; </b></span>

					            </div>
					          </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <div class="col-md-2 centered" style="">GST:</div>
				            <div class="col-md-3 row ">
				                <div class="col-md-12 input-group" id="totalSqFt">
				  
				                  <input name="servicetax_per" id="servicetax_per" style="width:50px;" type="text"class="form-control number"
                              value="{{ old('servicetax_per', $postdated->servicetaxPer ?? '') }}" onkeyup="totalTaxAmt();"onblur="totalTaxAmt();" />&nbsp;
				                  <span class="" style=""><b>%</b></span>
					
					                <input name="servicetax" id="servicetax"  type="text" class="form-control number" style="margin-left:42px;width: 90px;" value="{{ old('servicetax', $postdated->servicetax ?? '') }}" />&nbsp; 
					                <span class="" style="margin-left:2px;"><b> &#8377; </b></span>
					              </div>
					          </div>

                </div><br>


                <div class="row">
                  <div class="col-md-2 " style="">Registration:</div>
				  
                  <div class="col-md-3 row ">
				            <div class="col-md-12 input-group">
				              <input name="regist_per" id="regist_per"  style="width: 50px;"  type="text" class="form-control number " value="{{ old('regist_per', $postdated->regiChrgPer ?? '') }}" onkeyup="totalTaxAmt();" onblur="totalTaxAmt();" />&nbsp;
				              <span  style=""><b>%</b></span>
					
					            <input name="registration" id="registration" type="text" class="form-control number" style="margin-left:42px;width: 90px;" value="{{ old('registration', $postdated->regiChrg ?? '') }}" onkeyup="totalTaxAmt();" onblur="totalTaxAmt();" readonly/> &nbsp;
					            <span style="margin-left:2px;"><b> &#8377; </b></span>
					          </div>
					        </div>

                </div> <br>
                <div class="row">
                  
				            <div class="col-md-2 " ><b style="padding-left:0px;">Total Tax Amount:</b></div>
                    <div class="col-md-3" >
                        <input name="TotalTaxAmt"  readonly   id="TotalTaxAmt" type="text" class="form-control number" value="{{ old('TotalTaxAmt', $postdated->TaxamtTotal ?? '') }}" onkeyup="totalTaxAmt();" onblur="totalTaxAmt();"/> 
                    </div>
                    <div class="col-md-2 " style=""><b>Round Up:</b></div>
                    <div class="col-md-3" >
                      <input name="roundUp"  id="roundUp" type="text" class="form-control " value="{{ old('roundUp', $postdated->roundUp ?? '') }}" onkeyup="totalTaxAmt();" onblur="totalTaxAmt();"/> 
                    </div>
				  
				        </div><br>
                 <hr>
			  
              </div><br>
              <div class="row">
                  <div class="col-md-2 " style=""><b>Total Flat Amount:</b></div>
                  <div class="col-md-3" >
                      <input name="TotalFlatAmt"  readonly  id="TotalFlatAmt" type="text" class="form-control " value="{{ old('TotalFlatAmt', $postdated->TotalFlatAmt ?? '') }}" onkeyup="checkpay();" onblur="checkpay();"/> 
                  </div>
				  
				          <div class="col-md-2 " style=""><b>Downpayment:</b></div>
                  <div class="col-md-3" >
                    <input name="Downpayment" id="Downpayment" type="text" class="form-control number" value="{{ old('Downpayment', $booking_pay->amt_pay ?? '') }}" onkeyup="checkpay();" onblur="checkpay();"/> 
                  </div>
                  
                </div>
				        <br>
				        <div class="row" >
                  <div class="col-md-2 " style=""><b>Home Loan Sanction Amount:</b></div>
                  <div class="col-md-3" >
                    <input name="loan_sanction_amt" id="loan_sanction_amt" type="text" class="form-control number" value="{{ old('loan_sanction_amt', $postdated->loan_sanction_amt ?? '') }}" />
                  </div>
				  
				          <div class="col-md-2 " style=""><b>Bank Name:</b></div>
                  <div class="col-md-3" >
                    <input name="bankname"  id="bankname" type="text" class="form-control " value="{{ old('bankname', $postdated->bankname ?? '') }}" /> 
                  </div>
                  
                </div><br>
              <div class="row">
                  @if(!empty($slabs) && $slabs->pilnth != '')
                      <div class="col-md-2 ">pilnth :</div>
                      <div class="col-md-3">
                      <!--<input type="text" class="form-control input-sm number"   id="pilnth"   name="pilnth" value="{{ old('pilnth', $slabs->pilnth ?? '') }}" onkeyup="calcSlabTotal()" >-->
                      <input type="text" class="form-control input-sm number"   id="pilnth"   name="pilnth" value="{{ old('pilnth', !empty($postdated) ? $postdated->pilnth : ($slabs->pilnth ?? '')) }}" onkeyup="calcSlabTotal()" >
                      
                      </div>
                   @endif
                  
                @if(!empty($slabs) && $slabs->slab != '')
                <div class="col-md-2 ">slab : </div>
                      <div class="col-md-3">
                      <!--<input type="text" class="form-control input-sm number"  id="slab"  name="slab" value="{{ old('slab', $slabs->slab ?? '') }}" onkeyup="calcSlabTotal()" >-->
                       <input type="text" class="form-control input-sm number"  id="slab"  name="slab" value="{{ old('slab', !empty($postdated) ? $postdated->slab : ($slabs->slab ?? '')) }}" onkeyup="calcSlabTotal()" >
                      </div>
                 @endif
              </div><br>

              <div class="row" >	 
                @if(!empty($slabs) && $slabs->bricks != '')		 
                  <div class="col-md-2 ">bricks :</div>
                  <div class="col-md-3">
                    <!--<input type="text"  class="form-control input-sm number"  id="bricks"  name="bricks" value="{{ old('slab', $slabs->bricks ?? '') }}"  onkeyup="calcSlabTotal()">-->
                        <input type="text"  class="form-control input-sm number"  id="bricks"  name="bricks" value="{{ old('bricks', !empty($postdated) ? $postdated->bricks : ($slabs->bricks ?? '')) }}"  onkeyup="calcSlabTotal()">

                  </div>
		             @endif

                @if(!empty($slabs) && $slabs->plaster != '')	 
			            <div class="col-md-2 ">plaster :</div>
                  <div class="col-md-3">
                    <!--<input type="text"  class="form-control input-sm number " id="plaster"  name="plaster" value="{{ old('slab', $slabs->plaster ?? '') }}" onkeyup="calcSlabTotal()" >-->
                        <input type="text"  class="form-control input-sm number " id="plaster"  name="plaster" value="{{ old('plaster', !empty($postdated) ? $postdated->plaster : ($slabs->plaster ?? '')) }}" onkeyup="calcSlabTotal()" >

                  </div>
		           @endif
			
			        </div>
			        <br>
		 		 
			        <div class="row" >
                @if(!empty($slabs) && $slabs->floaring != '')	 			 
                  <div class="col-md-2 ">floaring :</div>
                  <div class="col-md-3">
                      <!--<input type="text"  class="form-control input-sm number" id="floaring"  name="floaring" value="{{ old('slab', $slabs->floaring ?? '') }}" onkeyup="calcSlabTotal()" >-->
                          <input type="text"  class="form-control input-sm number" id="floaring"  name="floaring" value="{{ old('floaring', !empty($postdated) ? $postdated->floaring : ($slabs->floaring ?? '')) }}" onkeyup="calcSlabTotal()" >

                  </div>
		             @endif
		            @if(!empty($slabs) && $slabs->plumbing != '')			 
                  <div class="col-md-2 ">plumbing :</div>
                  <div class="col-md-3">
                    <!--<input type="text"  class="form-control input-sm number " id="plumbing"  name="plumbing" value="{{ old('slab', $slabs->plumbing ?? '') }}" onkeyup="calcSlabTotal()" >-->
                        <input type="text"  class="form-control input-sm number " id="plumbing"  name="plumbing" value="{{ old('plumbing', !empty($postdated) ? $postdated->plumbing : ($slabs->plumbing ?? '')) }}" onkeyup="calcSlabTotal()" >

                  </div>
		            @endif
			        </div><br>
              <div class="row" >
                @if(!empty($slabs) && $slabs->project != '') 			 
                
                  <div class="col-md-2 goright">project :</div>
                  <div class="col-md-3">
                    <!--<input type="text"  class="form-control input-sm number " id="project"  name="project" value="{{ old('slab', $slabs->project ?? '') }}" onkeyup="calcSlabTotal()" >-->
                       <input type="text"  class="form-control input-sm number " id="project"  name="project" value="{{ old('project', !empty($postdated) ? $postdated->project : ($slabs->project ?? '')) }}" onkeyup="calcSlabTotal()" >

                  </div>

                 @endif
            
              </div><br>
                  <div class="row">
                    <div class="col-md-2">
                        <label><b>Total :</b></label>
                    </div>
                
                    <div class="col-md-3">
                        <input type="text"  class="form-control"  id="slab_total" name="slab_total" value="{{ old('slab_total', $slabs->total ?? 0) }}"  readonly>
                         <small id="slab_error" class="text-danger"></small>
                    </div>
                </div>
            
            <br><hr>

              <div class="row">
                <div class="table-responsive">
                    <table class="table" id="mytable">
                      <thead>
                        <tr>
                        
                          <th>Payment Method <small class="text-danger">*</small></th>
                          <th>Receipt No</th>
                          <th>Account No</th>
                          <th>Cheque No / Transaction ID</th>
                          <th>Amount</th>
                          <th>Narration</th>
                          
                          
                          
                        </tr>
                      </thead>
                      <tbody>
                        <tr>

                          <td>
                            <select name="Pay_type" id="Pay_type" class="form-control" onChange="check_type();">
                              <option value="">Select Payment Method</option>
                              <option value="cash" {{ old('payment_method', $booking_pay->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                              <option value="cheque" {{ old('payment_method', $booking_pay->payment_method ?? '') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                              <option value="e-payment" {{ old('payment_method', $booking_pay->payment_method ?? '') == 'e-payment' ? 'selected' : '' }}>E‑payment</option>
                            </select>
                            @error('payment_method')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                          </td>
                          <td>                                                        
                            <input type="text" style="width:90px;"  readonly name="receipt_no" id="receipt_no"  value="{{ old('receipt_no', $booking_pay->receipt_no ?? ($orderNo ?? '')) }}" class=" form-control required"/>
                          </td>
                          <td>
                            <div id="Ac" style="width:200px;">
                            <select name="account_no" id="account_no" class="form-control" >
                              <option value="">Select Account</option>
                              @foreach($banks as $bank)
                                <option value="{{ $bank->ID }}"
                                  {{ old('account_no', $booking_pay->account_no ?? '') == $bank->ID ? 'selected' : '' }}>
                                  {{ $bank->Name }}
                                </option>
                              @endforeach
                            </select>
                          </div>
                          </td>
                          <td>
                            <input type="text" name="cheque_no" id="cheque_no" class="form-control" value="{{ old('cheque_no', $booking_pay->cheque_no ?? '') }}">
                          </td>
                          
                          
                          <td>
                            <input type="number" name="amount_pay" id="amount_pay" readonly
                                class="form-control @error('amount_pay') is-invalid @enderror"
                                value="{{ old('amount_pay', $booking_pay->amt_pay ?? '') }}" onkeyup="chek_amt();">

                                <input type="hidden"  style=""name="amount_pay_old" id="amount_pay_old"  value="<?php //echo $pendingAmt;?>" class=" input-md form-control "/>

                            @error('amount_pay')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                          </td>
                          <td>
                            <textarea type="text" style="height: 34px;"    class=" input-medium form-control"  id="narration"  name="narration" >{{ old('narration', $booking_pay->narration ?? '') }}</textarea>
                          </td>
                          
                        </tr>
                      </tbody>
                    </table>
              </div>
              </div>

		

              <br>
              <button type="submit" class="btn btn-primary">{{ empty($postdated) ? 'Create' : 'Update' }}</button>
              <a href="{{ route('customer_booking') }}" class="btn btn-secondary">Cancel</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@section('scripts')
<script>

function toggleAgreement(show) {
    document.getElementById('aggrement_div').style.display = show ? 'block' : 'none';
}

// Maintain state on page load
document.addEventListener('DOMContentLoaded', function () {
    const agreementYes = document.querySelector('input[name="agreement_complete"][value="1"]');

    if (agreementYes && agreementYes.checked) {
        toggleAgreement(true);
    } else {
        toggleAgreement(false);
    }
});



function wing(){
    let Uid = $("#Uid").val() || '0000';
    let scheme = $("#schemeH").val();
    let wing = $("#typesrch").val();
    let type_bill = '';

    $.ajax({
        url: "{{ route('get.flats.no') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            schmid: scheme,
            Uid: Uid,
            wing: wing,
            type_bill: type_bill
        },
        success: function (data) {
            $("#div_fno").html(data);
        }
    });
}

function getFlatInfo() {
    let ftno = $('#fno').val();

    if (!ftno) return;

    $.ajax({
        url: "{{ route('get.flat.info') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            ftno: ftno
        },
        success: function (res) {
            $("#Attribute").html(res.FlatAttribute);
            $("#totalSqFt").html(res.TotalSqFt);
            $("#farea").val(res.TotalSqFt);
            $("#Other2").html(res.Other2);
            $("#Other1").html(res.Other1);
            $("#TerraceArea").html(res.Terrace);
            $("#area").html(res.Area);
            $("#floor").html(res.Floor);
            $("#wing").html(res.Wing);
            $("#fcatdiv").html(res.FlatType);
        }
    });
}

function show_ratesqft(val) {
 //alert(val);
  if (val=='1' || val=='2') {
    
    $('#show_rate').show();
    $('#show_rate').addClass('required');
    $('#TotalFlatAmt').prop('readonly', true);
    
    
  $('#RateSqft').addClass('form-control number');
  $('#light').addClass('form-control number');
  $('#parking').addClass('form-control number');
  $('#maintance').addClass('form-control number');
  $('#totalOthers').addClass('form-control number');
  $('#documentation').addClass('form-control number');
  $('#taxrate').addClass('form-control number');
  $('#regist_per').addClass('form-control number');
  $('#registration').addClass('form-control number');
  $('#vat_per').addClass('form-control number');
  $('#vat').addClass('form-control number');
  $('#servicetax_per').addClass('form-control number');
  $('#TotalTaxAmt').addClass('form-control number');
  $('#servicetax').addClass('form-control number');
  $('#sitedevcharges').addClass('form-control number');
  $('#stamp_amt').addClass('form-control number');
  $('#TotalFlatAmt').val('0');
  $('#Downpayment').val('0');
  $('#amount_pay').val('0');
  }
  else
  {
  $('#show_rate').hide();
  $('#show_rate').removeClass('required');
  $('#TotalFlatAmt').removeAttr('readonly');
    
  $('#RateSqft').removeClass('form-control number');
  $('#light').removeClass('form-control number');
  $('#parking').removeClass('form-control number');
  $('#maintance').removeClass('form-control number');
  $('#totalOthers').removeClass('form-control number');
  $('#documentation').removeClass('form-control number');
  $('#taxrate').removeClass('form-control number');
  $('#regist_per').removeClass('form-control number');
  $('#registration').removeClass('form-control number');
  $('#vat_per').removeClass('form-control number');
  $('#sitedevcharges').removeClass('form-control number');
  $('#vat').removeClass('form-control number');
  $('#stamp_amt').removeClass('form-control number');
  
  $('#servicetax_per').removeClass('form-control number');
  $('#TotalTaxAmt').removeClass('form-control number');
  $('#servicetax').removeClass('form-control number');
    
    
  $('#TotalFlatAmt').val('0');
  $('#Downpayment').val('0');   
  $('#RateSqft').val('0');
  $('#cost').val('0')
  //alert(j);
  $('#light').val('0');
  $('#parking').val('0');
  $('#maintance').val('0');
  $('#totalOthers').val('0');
  $('#documentation').val('0');
  $('#taxrate').val('0');
  $('#regist_per').val('0');
  $('#registration').val('0');
  $('#vat_per').val('0');
  $('#vat').val('0');
  $('#servicetax_per').val('0');
  $('#TotalTaxAmt').val('0');
  $('#servicetax').val('0');
  $('#amount_pay').val('0');
  }
}

function calccost(){
  var amt=0;
	var area=$("#farea").val();
	var rate=$("#RateSqft").val();
	if(area!='' && rate!=''){
	  amt=parseFloat(area)*parseFloat(rate);
		$("#costBSP").val(amt);
	
		}
		calOtherChrg();
}
function calOtherChrg(){
    var totalOthers=0;
    var light=$("#light").val();
    var parking=$("#parking").val();
    var maintance=$("#maintance").val();
    var documentation=$("#documentation").val();
    var sitedevcharges=$("#sitedevcharges").val();
    if (documentation=='') { documentation=0;}
    if (sitedevcharges=='') { sitedevcharges=0;}
    if (light=='') { light=0; }
    if (parking=='') { parking=0; }
    if (maintance=='') { maintance=0; }
    totalOthers=parseFloat(documentation)+parseFloat(sitedevcharges)+parseFloat(light)+parseFloat(parking)+parseFloat(maintance);
  
    $("#totalOthers").val(totalOthers.toFixed(2));
    
	totalTaxAmt();
}

function totalTaxAmt() {
  
		var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;
		var RergiCharg=0;
		var vatAmt=0;
		var servicetax=0;
		var TotalTaxAmt=0;
		var finalflatAmt=0;
		var AmtBsp=0;
		
		var stamp_amt=0;
		var taxrate=1;
		var totalsqft=$("#farea").val();
		var Bsp=$("#cost").val();
		var AmtBsp=$("#costBSP").val();
	    var totalOthers=$("#totalOthers").val();
	    var regist_per=$("#regist_per").val();
		var vat_per=$("#vat_per").val();
		var servicetax_per=$("#servicetax_per").val();
		var s_amt=$("#stamp_amt").val();
		
		if (taxrate=='') { taxrate=1;}
		if (totalsqft=='') { totalsqft=0;}
		if (Bsp=='') { Bsp==0;}
		
		if (totalOthers=='') { totalOthers=0;}
		if (regist_per=='') { regist_per=0;}
		if (vat_per=='') { vat_per=0;}
		if (servicetax_per=='') { servicetax_per=0;}
	    if(stamp_amt==''){stamp_amt=0;}
	    
		if( floatRegex.test(totalsqft) && floatRegex.test(regist_per))
		{
			 RergiCharg=parseFloat(s_amt)*parseFloat(parseFloat(regist_per)/100);
			 RergiCharg=Math.ceil(RergiCharg/100)*100;
		}
		
	   $("#registration").val(RergiCharg.toFixed(2));

		if(floatRegex.test(Bsp))
		{
		  vatAmt=(parseFloat(s_amt)*parseFloat(parseFloat(vat_per)/100));
		  vatAmt=Math.ceil(vatAmt/100)*100;
		}
		
		$("#vat").val(vatAmt.toFixed(2));

	    servicetax = $("#servicetax").val();
	   
	   if (servicetax=='') { servicetax=0;}
             // GST Calculation
        var gstAmt = 0;
        
        if(floatRegex.test(s_amt) && floatRegex.test(servicetax_per))
        {
            gstAmt = parseFloat(s_amt) * parseFloat(servicetax_per) / 100;
        }
        
        $("#servicetax").val(gstAmt.toFixed(2));
        
        servicetax = gstAmt;
        		if (vatAmt=='') { vatAmt=0;}
        		if (Bsp=='') { Bsp==0;}
        	
		if (RergiCharg=='') { RergiCharg=0;}
	   
	   
	 
	   TotalTaxAmt=parseFloat(RergiCharg)+parseFloat(vatAmt)+parseFloat(servicetax);
		
	   $("#TotalTaxAmt").val(TotalTaxAmt.toFixed(2));
	    if(floatRegex.test(Bsp) && floatRegex.test(totalOthers) && floatRegex.test(TotalTaxAmt) ) 
		{
	   finalflatAmt=parseFloat(Bsp)+parseFloat(totalOthers)+parseFloat(TotalTaxAmt)+parseFloat(AmtBsp-Bsp);
		}
		var roundup=$("#roundUp").val();
		if (roundup=='') { roundup=0;}
		finalflatAmt = parseFloat(finalflatAmt) - parseFloat(roundup);
	   $("#TotalFlatAmt").val(finalflatAmt.toFixed(2));
	  
	   
	  checkpay();
}

function checkpay() {
    var Downpayment=$("#Downpayment").val();
	var TotalFlatAmt=$("#TotalFlatAmt").val();
	if (parseFloat(Downpayment)>parseFloat(TotalFlatAmt)) {
      Downpayment=$("#Downpayment").val('');
	     alert("Plz Check Total Flat Amount!!!");
	}else{
	 $("#amount_pay").val(Downpayment);
	}
 }





function check_type() {
    let pay_method = $('#Pay_type').val();

    $.ajax({
        url: "{{ route('ajax.account.by.bookingpayment') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            pay_method: pay_method
        },
        success: function (response) {
            $('#Ac').html(response);
            getBalance();
        }
    });
    if(pay_method=="cash")
	{	$('#cheque_no').val('');
		$('#cheque_no').prop('readonly', true);
	    $('#cheque_no').removeClass("required");
	
	}
	
	else
	{
	 
		$('#account_no').removeAttr("readonly");
		$('#cheque_no').removeAttr("readonly");
	}
}

function diffrence(){
  var bspamt=$('#costBSP').val();
  var agreementAmt=$('#cost').val();
  var total=0;
  if(bspamt==''|| bspamt=='NAN'){bspamt=0;}
  if(agreementAmt==''){agreementAmt=0;}
  total=parseFloat(agreementAmt)-parseFloat(bspamt);
  $('#diff').val(total);
  $("#stamp_amt").val(agreementAmt);
}

function calcSlabTotal() {

    let pilnth   = parseFloat($('#pilnth').val()) || 0;
    let slab     = parseFloat($('#slab').val()) || 0;
    let bricks   = parseFloat($('#bricks').val()) || 0;
    let plaster  = parseFloat($('#plaster').val()) || 0;
    let floaring = parseFloat($('#floaring').val()) || 0;
    let plumbing = parseFloat($('#plumbing').val()) || 0;
    let project  = parseFloat($('#project').val()) || 0;

    let total = pilnth + slab + bricks + plaster + floaring + plumbing + project;

    if(total > 100) {

        $('#slab_error').html("Total slab percentage cannot be greater than 100.");

        $('#slab_total').val(total);

        return false;
    }

    $('#slab_error').html('');

    $('#slab_total').val(total);
}

$(document).ready(function(){
    calcSlabTotal();
});
</script>

<script>
window.onload = function () {

};
</script>


@endsection