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
      '<tr><td colspan="7" class="table-state"><i class="fa-regular fa-folder-open"></i> No guides match this filter.</td></tr>';
    return;
  }
  $("guideRows").innerHTML = state.records
    .map(
      (row) =>
        `<tr><td class="select-column"><input class="guide-checkbox" type="checkbox" value="${Number(row.id)}" aria-label="Select ${escapeHtml(row.full_name)}"></td><td class="guide-id">${escapeHtml(row.officer_id)}</td><td>${escapeHtml(row.full_name)}</td><td>${escapeHtml(row.nickname)}</td><td>${escapeHtml(row.nic || "—")}</td><td>${escapeHtml(row.languages)}</td><td class="address-cell">${escapeHtml(row.address || "—")}</td></tr>`,
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
    const query = new URLSearchParams({
      category: "All Categories",
      filter: $("typeFilter").value,
    });
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
      `<tr><td colspan="7" class="table-state error">${escapeHtml(error.message)}</td></tr>`;
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
$("printSelected").addEventListener("click", () => {
  const issue = $("issueDate").value,
    expiry = $("expiryDate").value;
  if (!issue || !expiry)
    return showMessage(
      "Choose both the issued date and expiry date before printing.",
      "error",
    );
  if (expiry <= issue)
    return showMessage(
      "The expiry date must be after the issued date.",
      "error",
    );
  $("printIds").value = [...state.selected].join(",");
  $("printIssueDate").value = issue;
  $("printExpiryDate").value = expiry;
  $("printForm").submit();
});
loadGuides();
