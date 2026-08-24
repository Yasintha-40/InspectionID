const state = { records: [], selected: new Set() };
const $ = (id) => document.getElementById(id);
function escapeHtml(v) {
  return String(v ?? "").replace(
    /[&<>'"]/g,
    (c) =>
      ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        "'": "&#039;",
        '"': "&quot;",
      })[c],
  );
}
function showMessage(message = "", type = "") {
  $("statusMessage").textContent = message;
  $("statusMessage").className = `status-message ${type}`;
}
function updateSelection() {
  const count = state.selected.size;
  $("selectedMetric").textContent = count;
  $("summaryCount").textContent = count;
  $("saveSelected").disabled = count === 0;
  $("printSelected").disabled = count === 0;
  document.querySelectorAll(".guide-checkbox").forEach((input) => {
    input.checked = state.selected.has(Number(input.value));
    input.closest("tr").classList.toggle("is-selected", input.checked);
  });
  $("selectAll").textContent =
    state.records.length &&
    state.records.every((r) => state.selected.has(Number(r.id)))
      ? "Clear"
      : "Select";
}
function renderRows() {
  if (!state.records.length) {
    $("guideRows").innerHTML =
      '<tr><td colspan="6" class="table-state"><i class="fa-regular fa-folder-open"></i> No guides match this filter.</td></tr>';
    return;
  }
  $("guideRows").innerHTML = state.records
    .map(
      (row) =>
        `<tr><td class="select-column"><input class="guide-checkbox" type="checkbox" value="${Number(row.id)}" aria-label="Select ${escapeHtml(row.full_name)}"></td><td class="guide-id">${escapeHtml(row.officer_id)}</td><td>${escapeHtml(row.full_name)}</td><td>${escapeHtml(row.nickname)}</td><td>${escapeHtml(row.nic || "—")}</td><td class="address-cell">${escapeHtml(row.address || "—")}</td></tr>`,
    )
    .join("");
  document.querySelectorAll(".guide-checkbox").forEach((input) =>
    input.addEventListener("change", () => {
      const id = Number(input.value);
      if (input.checked && state.selected.size >= 15) {
        input.checked = false;
        showMessage("You can select a maximum of 15 guide records.", "error");
        return;
      }
      input.checked ? state.selected.add(id) : state.selected.delete(id);
      showMessage();
      updateSelection();
    }),
  );
  updateSelection();
}
async function loadGuides() {
  $("loadGuides").disabled = true;
  $("loadGuides").innerHTML =
    '<i class="fa-solid fa-circle-notch fa-spin"></i> Loading';
  showMessage();
  try {
    const query = new URLSearchParams({ filter: $("typeFilter").value });
    const response = await fetch(`bulk_guides_api.php?${query}`);
    const data = await response.json();
    if (!response.ok || !data.success)
      throw new Error(data.message || "Unable to load guides.");
    state.records = data.records;
    const ids = new Set(state.records.map((r) => Number(r.id)));
    state.selected = new Set([...state.selected].filter((id) => ids.has(id)));
    $("totalRecords").textContent = Number(data.total).toLocaleString();
    $("readyRecords").textContent = Number(data.ready).toLocaleString();
    renderRows();
  } catch (error) {
    state.records = [];
    $("guideRows").innerHTML =
      `<tr><td colspan="6" class="table-state error">${escapeHtml(error.message)}</td></tr>`;
  } finally {
    $("loadGuides").disabled = false;
    $("loadGuides").textContent = "Load Guides";
  }
}
$("selectAll").addEventListener("click", () => {
  const all =
    state.records.length &&
    state.records.every((r) => state.selected.has(Number(r.id)));
  state.selected.clear();
  if (!all)
    state.records.slice(0, 15).forEach((r) => state.selected.add(Number(r.id)));
  if (state.records.length > 15 && !all)
    showMessage("The first 15 visible records were selected.", "info");
  else showMessage();
  updateSelection();
});
$("loadGuides").addEventListener("click", loadGuides);
function selectedDates() {
  const issue = $("issueDate").value;
  const expiry = $("expiryDate").value;
  if (!issue || !expiry) {
    showMessage("Choose both the issued date and expiry date before saving.", "error");
    return null;
  }
  if (expiry <= issue) {
    showMessage("The expiry date must be after the issued date.", "error");
    return null;
  }
  return { issue, expiry };
}
$("saveSelected").addEventListener("click", async () => {
  const dates = selectedDates();
  if (!dates || !state.selected.size) return;
  const button = $("saveSelected");
  button.disabled = true;
  button.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Saving';
  showMessage("Saving dates and generating missing QR codes...", "info");
  try {
    const body = new FormData();
    body.append("officer_ids", [...state.selected].join(","));
    body.append("issue_date", dates.issue);
    body.append("expiry_date", dates.expiry);
    const response = await fetch("bulk_save_api.php", { method: "POST", body });
    const data = await response.json();
    if (!response.ok || !data.success)
      throw new Error(data.message || "Unable to save the selected guides.");
    showMessage(data.message, "success");
    await loadGuides();
  } catch (error) {
    showMessage(error.message || "Unable to save the selected guides.", "error");
  } finally {
    button.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save & Generate QR';
    button.disabled = state.selected.size === 0;
  }
});
$("printSelected").addEventListener("click", () => {
  const dates = selectedDates();
  if (!dates) return;
  const { issue, expiry } = dates;
  $("printIds").value = [...state.selected].join(",");
  $("printIssueDate").value = issue;
  $("printExpiryDate").value = expiry;
  $("printForm").submit();
});
loadGuides();
