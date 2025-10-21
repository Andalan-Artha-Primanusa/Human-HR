@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block text-sm font-medium">Code</label>
    <input name="code" value="{{ old('code', $record->code) }}" class="mt-1 w-full border rounded-lg px-3 py-2" required>
    @error('code')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>
  <div>
    <label class="block text-sm font-medium">Name</label>
    <input name="name" value="{{ old('name', $record->name) }}" class="mt-1 w-full border rounded-lg px-3 py-2" required>
    @error('name')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>
  <div class="md:col-span-2">
    <label class="block text-sm font-medium">Legal Name</label>
    <input name="legal_name" value="{{ old('legal_name', $record->legal_name) }}" class="mt-1 w-full border rounded-lg px-3 py-2">
    @error('legal_name')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>

  <div>
    <label class="block text-sm font-medium">Email</label>
    <input name="email" type="email" value="{{ old('email', $record->email) }}" class="mt-1 w-full border rounded-lg px-3 py-2">
    @error('email')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>
  <div>
    <label class="block text-sm font-medium">Phone</label>
    <input name="phone" value="{{ old('phone', $record->phone) }}" class="mt-1 w-full border rounded-lg px-3 py-2">
    @error('phone')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>
  <div>
    <label class="block text-sm font-medium">Website</label>
    <input name="website" type="url" value="{{ old('website', $record->website) }}" class="mt-1 w-full border rounded-lg px-3 py-2">
    @error('website')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>
  <div>
    <label class="block text-sm font-medium">Logo Path</label>
    <input name="logo_path" value="{{ old('logo_path', $record->logo_path) }}" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="storage/logos/acme.png">
    @error('logo_path')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>

  <div class="md:col-span-2">
    <label class="block text-sm font-medium">Address</label>
    <textarea name="address" rows="3" class="mt-1 w-full border rounded-lg px-3 py-2">{{ old('address', $record->address) }}</textarea>
    @error('address')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>

  <div>
    <label class="block text-sm font-medium">City</label>
    <input name="city" value="{{ old('city', $record->city) }}" class="mt-1 w-full border rounded-lg px-3 py-2">
    @error('city')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>
  <div>
    <label class="block text-sm font-medium">Province</label>
    <input name="province" value="{{ old('province', $record->province) }}" class="mt-1 w-full border rounded-lg px-3 py-2">
    @error('province')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>
  <div>
    <label class="block text-sm font-medium">Country</label>
    <input name="country" value="{{ old('country', $record->country) }}" class="mt-1 w-full border rounded-lg px-3 py-2">
    @error('country')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>

  <div>
    <label class="block text-sm font-medium">Status</label>
    <select name="status" class="mt-1 w-full border rounded-lg px-3 py-2">
      <option value="active" @selected(old('status', $record->status) === 'active')>Active</option>
      <option value="inactive" @selected(old('status', $record->status) === 'inactive')>Inactive</option>
    </select>
    @error('status')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>
</div>

<div class="mt-6 flex items-center gap-3">
  <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
    Save
  </button>
  <a href="{{ url()->previous() }}" class="px-4 py-2 rounded-lg border">Cancel</a>
</div>
