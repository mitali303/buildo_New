@extends('backend.partials.master')

@section('title')
Advance Payment History
@endsection

@section('maincontent')

<main class="content">

    <div class="container-fluid p-0">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">
                Payment History -
                {{ $advance->user->name ?? '' }}
            </h3>

            <a href="{{ route('EmployeeAdvance') }}"
                class="btn btn-secondary">
                <i data-feather="arrow-left"></i> Back
            </a>
        </div>

        <!-- <div class="card">

        <div class="card-body">

            <div class="row text-center">

                <div class="col-md-2 mb-3">
                    <div class="border rounded p-3 bg-light">
                        <h6>Advance Amount</h6>
                        <h5 class="text-primary">
                            ₹ {{ number_format($advance->advance,2) }}
                        </h5>
                    </div>
                </div>

                <div class="col-md-2 mb-3">
                    <div class="border rounded p-3 bg-light">
                        <h6>EMI Amount</h6>
                        <h5 class="text-info">
                            ₹ {{ number_format($advance->emi_amount,2) }}
                        </h5>
                    </div>
                </div>

                <div class="col-md-2 mb-3">
                    <div class="border rounded p-3 bg-light">
                        <h6>Total EMI</h6>
                        <h5 class="text-dark">
                            {{ $advance->total_installments }}
                        </h5>
                    </div>
                </div>

                <div class="col-md-2 mb-3">
                    <div class="border rounded p-3 bg-light">
                        <h6>Paid EMI</h6>
                        <h5 class="text-success">
                            {{ $paidInstallments }}
                        </h5>
                    </div>
                </div>

                <div class="col-md-2 mb-3">
                    <div class="border rounded p-3 bg-light">
                        <h6>Pending EMI</h6>
                        <h5 class="text-danger">
                            {{ $remainingInstallments }}
                        </h5>
                    </div>
                </div>

                <div class="col-md-2 mb-3">
                    <div class="border rounded p-3 bg-light">
                        <h6>Balance</h6>
                        <h5 class="text-warning">
                            ₹ {{ number_format($advance->remaining_amount,2) }}
                        </h5>
                    </div>
                </div>

            </div>

        </div>

    </div> -->

        <div class="card mt-3">

            <div class="card-header">
                <h5 class="mb-0">Payment History Details</h5>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead class="table-dark">

                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Cheque No</th>
                                <th>Remaining Balance</th>
                                <th>Narration</th>
                            </tr>

                        </thead>

                        <tbody>

                            @forelse($payments as $key => $payment)

                            <tr>

                                <td>{{ $key + 1 }}</td>

                                <td>
                                    {{ date('d-m-Y',strtotime($payment->date)) }}
                                </td>

                                <td>
                                    ₹ {{ number_format($payment->advance,2) }}
                                </td>

                                <td>
                                    <span class="badge bg-success">
                                        {{ ucfirst($payment->payment_method) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $payment->cheque_no ?: '-' }}
                                </td>

                                <td>
                                    ₹ {{ number_format($payment->remaining_amount,2) }}
                                </td>

                                <td>
                                    {{ $payment->narration }}
                                </td>

                            </tr>

                            @empty

                            <tr>
                                <td colspan="7" class="text-center">
                                    No Payment History Found
                                </td>
                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</main>

@endsection