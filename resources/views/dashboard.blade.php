<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    {{-- <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

            </div>
        </div>
    </div> --}}
    
    {{-- {{dd($feedsWithdraw);}} --}}
    <div class="py-10 d-flex justify-content-center">
        <button id="suratPersetujuan" type="button" class="btn btn-outline-primary">Surat Persetujuan</button>
        <button id="withdrawBtn" type="button" class="btn btn-outline-primary">Request Withdraw</button>
        <button id="cancelBtn" type="button" class="btn btn-outline-primary">Request Cancel</button>
    </div>

    <div id="table1" class="container p-3">
        <table id="myTable" class=" table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Created_At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                {{-- {{dd($users)}} --}}
                @foreach ($users as $user)
                    <tr>
                        <th>{{$user->id}}</th>
                        <th>{{$user->name}}</th>
                        <th>{{$user->email}}</th>
                        <th>{{$user->phone_number}}</th>
                        <th>{{$user->role}}</th>
                        <th>
                            <a href="{{ asset('file/' . $user->file) }}" target="_blank">View File</a>
                        </th>
                        <th>{{$user->status}}</th>
                        <th>{{$user->created_at}}</th>
                        <th>
                            <a href="{{ route('acceptApproval', ['id' => $user->id]) }}" class="btn btn-sm btn-outline-success">Accept</a>
                            <a href="{{ route('rejectApproval', ['id' => $user->id]) }}" class="btn btn-sm btn-outline-danger">Reject</a>
                        </th>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div id="table2" class="container p-3">
        <table id="myTable2" class=" table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Trip ID</th>
                    <th>Agent</th>
                    <th>Title</th>
                    <th>Location</th>
                    <th>Meeting Point</th>
                    <th>Unit</th>
                    <th>Total Price</th>
                    <th>Payment Account</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Date Start</th>
                    <th>Date End</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($feedsWithdraw as $feed)
              
                <tr>
                    <th>{{$feed->id}}</th>
                    <th>{{$feed->feed_id}}</th>
                    <th>{{$feed->name}}</th>
                    <th>{{$feed->title}}</th>
                    <th>{{$feed->location}}</th>
                    <th>{{$feed->meeting_point}}</th>
                    <th>{{$feed->fee}}</th>
                    <th>{{$feed->total_price}}</th>
                    <th>{{$feed->payment_account}}</th>
                    <th>
                        <a href="{{ asset('file/' . $feed->file) }}" target="_blank">View File</a>
                    </th>
                    <th>{{$feed->status}}</th>
                    <th>{{$feed->date_start}}</th>
                    <th>{{$feed->date_end}}</th>
                    <th>
                        <button type="button" class="btn btn-sm btn-outline-success">Accept</button>
                        <button type="button" class="btn btn-sm btn-outline-danger">Reject</button>
                    
                    </th>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div id="table3" class="container p-3">
        <table id="myTable3" class=" table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Trip ID</th>
                    <th>Agent</th>
                    <th>Title</th>
                    <th>Location</th>
                    <th>Unit</th>
                    <th>Payment Account</th>
                    <th>File</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($feedsCancel as $feed)
              
                <tr>
                    <th>{{$feed->id}}</th>
                    <th>{{$feed->feed_id}}</th>
                    <th>{{$feed->name}}</th>
                    <th>{{$feed->title}}</th>
                    <th>{{$feed->location}}</th>
                    <th>{{$feed->fee}}</th>
                    <th>{{$feed->payment_account}}</th>
                    <th>
                        <a href="{{ asset('file/' . $feed->file) }}" target="_blank">View File</a>
                    </th>
                    <th>{{$feed->reason}}</th>
                    <th>{{$feed->status}}</th>
                    <th>
                        <button type="button" class="btn btn-sm btn-outline-success">Accept</button>
                        <button type="button" class="btn btn-sm btn-outline-danger">Reject</button>
                    
                    </th>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

</x-app-layout>
