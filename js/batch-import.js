const elements = {
  file: document.getElementById("workbookFile"),
  dropZone: document.getElementById("dropZone"),
  selectedFile: document.getElementById("selectedFile"),
  validateButton: document.getElementById("validateButton"),
  uploadPanel: document.getElementById("uploadPanel"),
  previewPanel: document.getElementById("previewPanel"),
  resultPanel: document.getElementById("resultPanel"),
  toast: document.getElementById("toast"),
};

let selectedWorkbook = null;
let stagingToken = "";

function escapeHtml(value) {
  return String(value ?? "").replace(
    /[&<>'"]/g,
    (character) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#039;", '"': "&quot;" })[character],
  );
}

function showToast(message) {
  elements.toast.textContent = message;
  elements.toast.classList.add("is-visible");
  window.setTimeout(() => elements.toast.classList.remove("is-visible"), 4000);
}

function setStep(activeStep) {
  document.querySelectorAll(".step").forEach((step) => {
    const number = Number(step.dataset.step);
    step.classList.toggle("is-active", number === activeStep);
    step.classList.toggle("is-complete", number < activeStep);
    const circle = step.querySelector(":scope > span");
    circle.innerHTML = number < activeStep ? '<i class="fa-solid fa-check"></i>' : number;
  });
}

function resetImport() {
  selectedWorkbook = null;
  stagingToken = "";
  elements.file.value = "";
  elements.selectedFile.hidden = true;
  elements.validateButton.disabled = true;
  elements.uploadPanel.hidden = false;
  elements.previewPanel.hidden = true;
  elements.resultPanel.hidden = true;
  setStep(1);
  window.scrollTo({ top: 0, behavior: "smooth" });
}

function selectFile(file) {
  if (!file) return;
  if (!file.name.toLowerCase().endsWith(".xlsx")) return showToast("Please select an .xlsx Excel workbook.");
  if (file.size > 5 * 1024 * 1024) return showToast("The workbook must be smaller than 5 MB.");
  selectedWorkbook = file;
  document.getElementById("fileName").textContent = file.name;
  document.getElementById("fileSize").textContent = `${(file.size / 1024).toFixed(1)} KB`;
  elements.selectedFile.hidden = false;
  elements.validateButton.disabled = false;
}

function renderPreview(data) {
  stagingToken = data.token;
  document.getElementById("previewDescription").textContent = `${data.sheet} · ${data.counts.total} data rows found`;
  document.getElementById("totalRows").textContent = data.counts.total;
  document.getElementById("validRows").textContent = data.counts.valid;
  document.getElementById("duplicateRows").textContent = data.counts.duplicates;
  document.getElementById("errorRows").textContent = data.counts.errors;
  document.getElementById("validationAlert").textContent = data.counts.errors
    ? `${data.counts.errors} row(s) contain errors and will not be imported. Review the highlighted records below.`
    : "";
  document.getElementById("previewRows").innerHTML = data.records
    .map((record) => {
      const detail = record.errors.length ? record.errors.join(", ") : record.status;
      return `<tr><td>${record.row_number}</td><td><span class="record-status ${record.status}" title="${escapeHtml(detail)}">${escapeHtml(record.status)}</span></td><td>${escapeHtml(record.full_name)}</td><td>${escapeHtml(record.nic || "—")}</td><td>${escapeHtml(record.email || "—")}</td><td>${escapeHtml(record.address || "—")}</td></tr>`;
    })
    .join("");
  elements.uploadPanel.hidden = true;
  elements.previewPanel.hidden = false;
  setStep(2);
}

async function validateWorkbook() {
  if (!selectedWorkbook) return;
  elements.validateButton.disabled = true;
  elements.validateButton.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Validating';
  const form = new FormData();
  form.append("action", "preview");
  form.append("workbook", selectedWorkbook);
  try {
    const response = await fetch("batch_import_api.php", { method: "POST", body: form });
    const data = await response.json();
    if (!response.ok || !data.success) throw new Error(data.message || "Validation failed.");
    renderPreview(data);
  } catch (error) {
    showToast(error.message);
  } finally {
    elements.validateButton.disabled = false;
    elements.validateButton.innerHTML = 'Validate workbook <i class="fa-solid fa-arrow-right"></i>';
  }
}

async function commitImport() {
  const button = document.getElementById("commitButton");
  const policy = document.querySelector('input[name="duplicatePolicy"]:checked').value;
  button.disabled = true;
  button.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Importing records';
  const form = new FormData();
  form.append("action", "commit");
  form.append("token", stagingToken);
  form.append("duplicate_policy", policy);
  try {
    const response = await fetch("batch_import_api.php", { method: "POST", body: form });
    const data = await response.json();
    if (!response.ok || !data.success) throw new Error(data.message || "Import failed.");
    document.getElementById("insertedCount").textContent = data.inserted;
    document.getElementById("updatedCount").textContent = data.updated;
    document.getElementById("skippedCount").textContent = data.skipped;
    document.getElementById("resultMessage").textContent = `Import job #${data.job_id} was saved successfully. ${data.failed} invalid row(s) were excluded.`;
    elements.previewPanel.hidden = true;
    elements.resultPanel.hidden = false;
    setStep(3);
  } catch (error) {
    showToast(error.message);
    button.disabled = false;
    button.innerHTML = 'Import valid records <i class="fa-solid fa-database"></i>';
  }
}

elements.file.addEventListener("change", () => selectFile(elements.file.files[0]));
elements.dropZone.addEventListener("dragover", (event) => { event.preventDefault(); elements.dropZone.classList.add("is-dragging"); });
elements.dropZone.addEventListener("dragleave", () => elements.dropZone.classList.remove("is-dragging"));
elements.dropZone.addEventListener("drop", (event) => { event.preventDefault(); elements.dropZone.classList.remove("is-dragging"); selectFile(event.dataTransfer.files[0]); });
document.getElementById("removeFile").addEventListener("click", (event) => { event.preventDefault(); resetImport(); });
document.getElementById("chooseAnother").addEventListener("click", resetImport);
document.getElementById("cancelPreview").addEventListener("click", resetImport);
document.getElementById("importAnother").addEventListener("click", resetImport);
elements.validateButton.addEventListener("click", validateWorkbook);
document.getElementById("commitButton").addEventListener("click", commitImport);
