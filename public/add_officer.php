<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Officer - Inspection ID System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="../css/add-officer.css">
</head>
<body>
  <header class="page-header">
    <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back</a>
    <div><h1>ADD OFFICER</h1><p>Create a new inspection officer record</p></div>
  </header>

  <main class="page-shell">
    <form id="addOfficerForm" class="form-card" enctype="multipart/form-data" novalidate>
      <div class="form-heading"><span>Officer details</span><h2>New officer information</h2><p>Fields marked with * are required.</p></div>
      <div id="formMessage" class="form-message" role="alert" aria-live="polite"></div>
      <div class="form-grid">
        <label class="wide">Full name *<input name="full_name" maxlength="255" required autocomplete="name"></label>
        <label>NIC number *<input name="nic" maxlength="30" required autocomplete="off"></label>
        <label>Email address<input name="email" type="email" maxlength="150" autocomplete="email"></label>
        <label>Nickname<input name="nickname" maxlength="100"></label>
        <label>Designation<input name="designation" maxlength="100" value="Inspection Officer"></label>
        <label>Phone number<input name="phone" type="tel" maxlength="30" autocomplete="tel"></label>
        <label>Status
          <select name="status">
            <option value="Active" selected>Active</option>
            <option value="Inactive">Inactive</option>
            <option value="Expired">Expired</option>
            <option value="Suspended">Suspended</option>
          </select>
        </label>
        <label>Registration date<input name="issue_date" type="date"></label>
        <label>Expiry date<input name="expiry_date" type="date"></label>
        <label class="wide">Registered address<textarea name="address" rows="4"></textarea></label>
        <label class="wide photo-field">Officer photo
          <span class="photo-upload">
            <input id="officerPhoto" name="photo" type="file" accept="image/jpeg,image/png">
            <span class="photo-placeholder" id="photoPlaceholder"><i class="fa-solid fa-image"></i><b>Choose a JPG or PNG image</b><small>Maximum 5 MB. It will be saved using the NIC number.</small></span>
            <img id="photoPreview" alt="Selected officer photo preview" hidden>
          </span>
        </label>
      </div>
      <div class="form-actions"><a href="dashboard.php" class="secondary-button">Cancel</a><button id="submitButton" type="submit"><i class="fa-solid fa-user-plus"></i> Add officer</button></div>
    </form>
  </main>
  <script src="../js/add-officer.js"></script>
</body>
</html>
