@extends('layouts.master')

@section('page_title', 'Activity Logs')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Activity Logs</h3>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-body table-responsive">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="logs_table">
            <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <tr>
                    <th>Waktu</th>
                    <th>Pelaku</th>
                    <th>Modul</th>
                    <th>Aksi</th>
                    <th>Deskripsi</th>
                    <th>IP Address</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr
                  data-id="{{ $log->id }}"
                  data-user="{{ $log->user->name ?? '-' }}"
                  data-module="{{ $log->module }}"
                  data-action="{{ $log->action }}"
                  data-description='@json($log->description)'
                  data-ip="{{ $log->ip_address }}"
                  data-user_agent='@json($log->user_agent)'
                  data-old_values='@json($log->old_values)'
                  data-new_values='@json($log->new_values)'
                  data-created_at="{{ $log->created_at?->format('d M Y H:i') }}"
                >
                    <td data-order="{{ $log->created_at?->timestamp }}">{{ $log->created_at?->format('d M Y H:i') }}</td>
                    <td>{{ $log->user->name ?? '-' }}</td>
                    <td>{{ $log->module }}</td>
                    <td>
                        @php
                            $map = [
                                'IMPORT_EXCEL' => 'primary',
                                'CREATE' => 'success',
                                'UPDATE' => 'warning',
                                'DELETE' => 'danger',
                                'LOGIN' => 'info',
                            ];
                            $cls = $map[$log->action] ?? 'secondary';
                        @endphp
                        <span class="badge badge-light-{{ $cls }}">{{ $log->action }}</span>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($log->description, 80) }}</td>
                    <td>{{ $log->ip_address }}</td>
                    <td class="text-end">
                        <button class="btn btn-primary btn-sm btnDetailLog"><i class="bi bi-eye"></i> Detail</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="logDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Aktivitas</h5>
        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <div class="modal-body">
        <div class="row g-4">
          <div class="col-md-6">
            <div><strong>Waktu:</strong> <span id="d_time">-</span></div>
            <div><strong>Pelaku:</strong> <span id="d_user">-</span></div>
            <div><strong>Modul:</strong> <span id="d_module">-</span></div>
            <div><strong>Aksi:</strong> <span id="d_action" class="badge">-</span></div>
            <div><strong>IP Address:</strong> <span id="d_ip">-</span></div>
          </div>
          <div class="col-md-6">
            <div><strong>User Agent:</strong></div>
            <pre class="bg-light p-3 small" id="d_ua"></pre>
          </div>
          <div class="col-12">
            <div class="d-flex flex-wrap gap-4">
              <div class="flex-fill" style="min-width:300px;">
                <div class="fw-bold mb-2">Data Lama</div>
                <pre class="bg-light p-3 small" id="d_old"></pre>
              </div>
              <div class="flex-fill" style="min-width:300px;">
                <div class="fw-bold mb-2">Data Baru</div>
                <pre class="bg-light p-3 small" id="d_new"></pre>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
  const dt = $('#logs_table').DataTable({ pageLength: 10, ordering: true, order: [[0, 'desc']] });

  $('#logs_table').on('click', '.btnDetailLog', function(){
    const row = $(this).closest('tr');
    const action = row.data('action');
    const badgeMap = { IMPORT_EXCEL: 'badge-light-primary', CREATE: 'badge-light-success', UPDATE: 'badge-light-warning', DELETE: 'badge-light-danger', LOGIN: 'badge-light-info' };

    $('#d_time').text(row.data('created_at'));
    $('#d_user').text(row.data('user'));
    $('#d_module').text(row.data('module'));
    $('#d_action').text(action).attr('class', 'badge ' + (badgeMap[action] || 'badge-light-secondary'));
    $('#d_ip').text(row.data('ip'));

    try { $('#d_ua').text(JSON.parse(row.attr('data-user_agent'))); } catch (e) { $('#d_ua').text(row.data('user_agent') || '-'); }

    // old/new values pretty print
    function pretty(v){
      try {
        const obj = JSON.parse(row.attr(v));
        return JSON.stringify(obj, null, 2);
      } catch (e) {
        return row.attr(v) || '';
      }
    }
    $('#d_old').text(pretty('data-old_values'));
    $('#d_new').text(pretty('data-new_values'));

    const modal = new bootstrap.Modal(document.getElementById('logDetailModal'));
    modal.show();
  });
});
</script>
@endpush
