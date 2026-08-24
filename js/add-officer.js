const form = document.getElementById("addOfficerForm");
const message = document.getElementById("formMessage");
const submitButton = document.getElementById("submitButton");
const photoInput = document.getElementById("officerPhoto");
const photoPreview = document.getElementById("photoPreview");
const photoPlaceholder = document.getElementById("photoPlaceholder");
let previewUrl = "";

function showMessage(text, type) {
  message.textContent = text;
  message.className = `form-message ${type}`;
}

photoInput.addEventListener("change", () => {
  if (previewUrl) URL.revokeObjectURL(previewUrl);
  const file = photoInput.files[0];
  if (!file) {
    photoPreview.hidden = true;
    photoPlaceholder.hidden = false;
    return;
  }
  if (!['image/jpeg', 'image/png'].includes(file.type) || file.size > 5 * 1024 * 1024) {
    photoInput.value = "";
    showMessage("Choose a JPG or PNG photo no larger than 5 MB.", "error");
    return;
  }
  previewUrl = URL.createObjectURL(file);
  photoPreview.src = previewUrl;
  photoPreview.hidden = false;
  photoPlaceholder.hidden = true;
  showMessage("", "");
});

form.addEventListener("submit", async (event) => {
  event.preventDefault();
  if (!form.reportValidity()) return;
  const data = new FormData(form);
  const issueDate = data.get("issue_date");
  const expiryDate = data.get("expiry_date");
  if ((issueDate || expiryDate) && (!issueDate || !expiryDate || expiryDate <= issueDate)) {
    showMessage("Expiry date must be after the registration date.", "error");
    return;
  }
  submitButton.disabled = true;
  submitButton.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Saving';
  showMessage("", "");
  try {
    const response = await fetch("add_officer_api.php", { method: "POST", body: data });
    const result = await response.json();
    if (!response.ok || !result.success) throw new Error(result.message || "Unable to add officer.");
    form.reset();
    photoPreview.hidden = true;
    photoPlaceholder.hidden = false;
    showMessage(`${result.message} Officer ID: ${result.officer_id}`, "success");
  } catch (error) {
    showMessage(error.message, "error");
  } finally {
    submitButton.disabled = false;
    submitButton.innerHTML = '<i class="fa-solid fa-user-plus"></i> Add officer';
  }
});
