<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel='stylesheet' href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>

<body class="font-sans antialiased">
    <x-banner />

    <div class="min-h-screen bg-gray-100">
        @livewire('navigation-menu')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    @stack('modals')

    @livewireScripts
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#myTable').DataTable();
            $('#myTable2').DataTable();
            $('#myTable3').DataTable();
            console.log("hello");
        });

        document.addEventListener("DOMContentLoaded", function() {
        // Ambil tombol Request Withdraw dan Request Cancel
        const suratPersetujuan = document.getElementById("suratPersetujuan");
        const withdrawBtn = document.getElementById("withdrawBtn");
        const cancelBtn = document.getElementById("cancelBtn");

        // Ambil kedua tabel
        const table1 = document.getElementById("table1");
        const table2 = document.getElementById("table2");
        const table3 = document.getElementById("table3");

        // Sembunyikan tabel 2 dan 3 saat halaman dimuat
        table2.style.display = "none";
        table3.style.display = "none";

        suratPersetujuan.addEventListener("click", function() {
            table1.style.display = "block";
            table2.style.display = "none";
            table3.style.display = "none";
        });

        // Event listener untuk tombol Request Withdraw
        withdrawBtn.addEventListener("click", function() {
            table1.style.display = "none";
            table2.style.display = "block";
            table3.style.display = "none";
        });

        // Event listener untuk tombol Request Cancel
        cancelBtn.addEventListener("click", function() {
            table1.style.display = "none";
            table2.style.display = "none";
            table3.style.display = "block";
        });
    });
    </script>
</body>

</html>
