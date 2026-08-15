<form method="POST" action="{{ route('mahasiswa.pendaftaran.daftar-ulang', $pendaftaran) }}" enctype="multipart/form-data" class="space-y-4">
    @csrf

    @if ($pendaftaran->promo && $pendaftaran->promo->isBerlaku() && $pendaftaran->promo->berlakuUntuk('spp'))
        <div class="flex items-start gap-2 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" />
            <div>
                Promo <span class="font-semibold">{{ $pendaftaran->promo->kode }}</span> aktif untuk SPP.
                Potongan <span class="font-semibold">{{ $pendaftaran->promo->labelPotongan() }}</span>.
                Masukkan nominal yang sudah Anda bayarkan setelah potongan.
            </div>
        </div>
    @endif

    <div>
        <x-ui-label for="nominal" required>Nominal yang Dibayar (Rp)</x-ui-label>
        <div class="mt-2">
            <x-ui-input type="number" name="nominal" id="nominal" :value="old('nominal')" step="0.01" min="0" required placeholder="Contoh: 5000000" />
        </div>
        @error('nominal')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <x-ui-label for="bukti_bayar" required>Bukti Pembayaran</x-ui-label>
        <div class="mt-2">
            <input type="file" name="bukti_bayar" id="bukti_bayar" required accept=".jpg,.jpeg,.png,.pdf"
                   class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
        </div>
        @error('bukti_bayar')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <x-ui-button variant="success" icon="check" class="w-full">Kirim Bukti Pembayaran</x-ui-button>
</form>
