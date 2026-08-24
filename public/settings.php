<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings - Inspection ID System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="../css/settings.css">
</head>
<body>
  <header class="settings-header">
    <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i><span>Back</span></a>
    <div><h1>SETTINGS</h1><p>Manage local storage paths</p></div>
  </header>

  <main class="settings-shell">
    <div class="settings-heading">
      <div><span>Administration</span><h2>Storage settings</h2><p>Local storage paths are saved for this server.</p></div>
      <div id="saveMessage" class="save-message" role="status" aria-live="polite"></div>
    </div>

    <div id="settingsForm">
      <section class="settings-card path-settings-card">
        <div class="section-icon"><i class="fa-solid fa-folder-tree" aria-hidden="true"></i></div>
        <div class="section-copy"><h3>Local storage paths</h3><p>Choose where the server reads officer photos and saves generated QR images.</p></div>
        <div class="path-fields">
          <label>QR image folder
            <span class="path-input-row"><input name="qr_directory" id="qrDirectory" value="D:\PHOTOS\QR" spellcheck="false" autocomplete="off"><i class="path-status" id="qrPathStatus" aria-label="Path status"></i></span>
          </label>
          <label>Officer photo folder
            <span class="path-input-row"><input name="photo_directory" id="photoDirectory" value="D:\PHOTOS\id_number" spellcheck="false" autocomplete="off"><i class="path-status" id="photoPathStatus" aria-label="Path status"></i></span>
          </label>
          <div class="path-actions"><button type="button" class="danger-button" id="resetPaths"><i class="fa-solid fa-trash-can" aria-hidden="true"></i> Delete custom paths</button><button type="button" class="update-paths-button" id="updatePaths"><i class="fa-solid fa-rotate" aria-hidden="true"></i> Update paths</button></div>
          <p class="path-help">Deleting custom paths only restores the defaults. It never deletes folders or files.</p>
        </div>
      </section>

    </div>
  </main>
  <script>
    const message = document.getElementById('saveMessage');

    const qrDirectory = document.getElementById('qrDirectory');
    const photoDirectory = document.getElementById('photoDirectory');
    const updatePaths = document.getElementById('updatePaths');
    const resetPaths = document.getElementById('resetPaths');

    function showPathStatus(status = {}) {
      [['qrPathStatus', status.qr_directory], ['photoPathStatus', status.photo_directory]].forEach(([id, exists]) => {
        const icon = document.getElementById(id);
        icon.className = `path-status fa-solid ${exists ? 'fa-circle-check exists' : 'fa-triangle-exclamation missing'}`;
        icon.title = exists ? 'Folder found' : 'Folder not found; it will be created when needed';
      });
    }

    function loadPaths() {
      fetch('settings_api.php').then(response => response.json()).then(data => {
        if (!data.success) throw new Error(data.message);
        qrDirectory.value = data.paths.qr_directory;
        photoDirectory.value = data.paths.photo_directory;
        showPathStatus(data.status);
      }).catch(error => message.textContent = error.message || 'Unable to load local paths.');
    }

    updatePaths.addEventListener('click', () => {
      const body = new URLSearchParams({ action: 'update', qr_directory: qrDirectory.value, photo_directory: photoDirectory.value });
      fetch('settings_api.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' }, body })
        .then(response => response.json().then(data => ({ response, data }))).then(({ response, data }) => {
          if (!response.ok || !data.success) throw new Error(data.message);
          message.textContent = data.message;
          showPathStatus(data.status);
        }).catch(error => message.textContent = error.message || 'Unable to update paths.');
    });

    resetPaths.addEventListener('click', () => {
      if (!confirm('Delete custom path settings and restore the defaults? No folders or files will be deleted.')) return;
      const body = new URLSearchParams({ action: 'reset' });
      fetch('settings_api.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' }, body })
        .then(response => response.json().then(data => ({ response, data }))).then(({ response, data }) => {
          if (!response.ok || !data.success) throw new Error(data.message);
          qrDirectory.value = data.paths.qr_directory;
          photoDirectory.value = data.paths.photo_directory;
          message.textContent = data.message;
          loadPaths();
        }).catch(error => message.textContent = error.message || 'Unable to reset paths.');
    });

    loadPaths();
  </script>
</body>
</html>
