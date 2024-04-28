@push('head')
    <link
        href="/favicon.ico"
        id="favicon"
        rel="icon"
    >
@endpush

<div class="h2 d-flex align-items-center">
    @auth
        <x-orchid-icon path="bs.house" class="d-inline d-xl-none"/>
    @endauth

    <p class="my-0 {{ auth()->check() ? 'd-none d-xl-block' : '' }}">
        <img style="width:180px" src="https://eurorentcars.md/wp-content/uploads/2024/01/msg5453929200-35538-removebg-preview.png">
    </p>
</div>
