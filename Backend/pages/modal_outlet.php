<!-- Modal Tambah Outlet -->
<div class="modal fade" id="modalTambahOutlet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="" method="POST">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">Tambah Outlet Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Outlet</label>
                        <input type="text" class="form-control" name="nama" required placeholder="Contoh: Cabang Setiabudi">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Alamat</label>
                        <textarea class="form-control" name="alamat" rows="2" required placeholder="Alamat lengkap outlet"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">No. Telepon</label>
                        <input type="text" class="form-control" name="tlp" placeholder="Contoh: 021555888">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_outlet" class="btn btn-burgundy rounded-pill px-4">Simpan Outlet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Outlet -->
<div class="modal fade" id="modalEditOutlet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="" method="POST">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">Edit Data Outlet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Outlet</label>
                        <input type="text" class="form-control" id="edit-nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Alamat</label>
                        <textarea class="form-control" id="edit-alamat" name="alamat" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">No. Telepon</label>
                        <input type="text" class="form-control" id="edit-tlp" name="tlp">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_outlet" class="btn btn-burgundy rounded-pill px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>