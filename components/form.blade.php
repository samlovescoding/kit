<form {{ $attributes->merge([
    'method' => 'post',
]) }}>
  @csrf
  {{ $slot }}
</form>
