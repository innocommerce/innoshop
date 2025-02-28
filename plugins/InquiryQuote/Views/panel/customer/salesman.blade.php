<x-common-form-select title="所属业务员" name="admin_id" :options="$salesmen" key="id" label="name"
                      value="{{ old('admin_id', $customer->admin_id) }}"/>