@extends('layouts.master')

@section('page_title', 'Activity Logs')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Activity Logs</h3>
    </div>

    @if (session('error'))
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
                <tbody></tbody>
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
            function esc(v) {
                return $('<div/>').text(v == null ? '' : String(v)).html();
            }

            const dt = $('#logs_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ordering: true,
                order: [
                    [0, 'desc']
                ],
                ajax: {
                    url: '{{ route('activity-logs.data') }}',
                    type: 'GET'
                },
                columns: [{
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data) {
                            return esc(data);
                        }
                    },
                    {
                        data: 'user',
                        name: 'user',
                        render: function(data) {
                            return esc(data);
                        }
                    },
                    {
                        data: 'module',
                        name: 'module',
                        render: function(data) {
                            return esc(data);
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        render: function(data) {
                            const map = {
                                IMPORT_EXCEL: 'primary',
                                CREATE: 'success',
                                UPDATE: 'warning',
                                DELETE: 'danger',
                                LOGIN: 'info'
                            };
                            const cls = map[data] || 'secondary';
                            return '<span class="badge badge-light-' + cls + '">' + esc(data) +
                                '</span>';
                        }
                    },
                    {
                        data: 'description',
                        name: 'description',
                        render: function(data) {
                            return esc(data);
                        }
                    },
                    {
                        data: 'ip_address',
                        name: 'ip_address',
                        render: function(data) {
                            return esc(data);
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function() {
                            return '<button class="btn btn-primary btn-sm btnDetailLog"><i class="bi bi-eye"></i> Detail</button>';
                        }
                    }
                ]
            });

            $('#logs_table').on('click', '.btnDetailLog', function() {
                const tr = $(this).closest('tr');
                const rowData = dt.row(tr).data() || {};
                const action = rowData.action;
                const badgeMap = {
                    IMPORT_EXCEL: 'badge-light-primary',
                    CREATE: 'badge-light-success',
                    UPDATE: 'badge-light-warning',
                    DELETE: 'badge-light-danger',
                    LOGIN: 'badge-light-info'
                };

                $('#d_time').text(rowData.created_at || '-');
                $('#d_user').text(rowData.user || '-');
                $('#d_module').text(rowData.module || '-');
                $('#d_action').text(action).attr('class', 'badge ' + (badgeMap[action] ||
                    'badge-light-secondary'));
                $('#d_ip').text(rowData.ip_address || '-');

                $('#d_ua').text(rowData.user_agent || '-');

                function pretty(obj) {
                    if (obj == null) return '';
                    try {
                        return JSON.stringify(obj, null, 2);
                    } catch (e) {
                        return String(obj);
                    }
                }
                $('#d_old').text(pretty(rowData.old_values));
                $('#d_new').text(pretty(rowData.new_values));

                const modal = new bootstrap.Modal(document.getElementById('logDetailModal'));
                modal.show();
            });
        });
    </script>
@endpush
