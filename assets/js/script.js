document.addEventListener("DOMContentLoaded", function () {
	// for all forms
	document.querySelectorAll("form:not([data-allow-submit], [data-submit-block])").forEach((form) => {
		const msg = form.querySelector(".msg");

		form.addEventListener("submit", async function (e) {
			e.preventDefault();

			const action = form.action,
				method = form.dataset.method ?? form.method,
				formData = new FormData(form),
				body = [];
			let iterator = formData.keys();

			msg.innerHTML = "";
			while (!(key = iterator.next()).done) {
				console.log(key);
				body.push(key.value + "=" + formData.getAll(key.value));
			}

			try {
				let res;
				if (method == "GET" || method == "HEAD") {
					res = await fetch(action, {
						method: method,
					});
				} else {
					res = await fetch(action, {
						method: method,
						body: body.join("&"),
					});
				}

				var data;
				if (res.headers.get("content-type") == "application/json") {
					data = await res.json();
				} else {
					data = await res.text();
				}

				if (res.ok) {
					msg.classList.add("success");
				} else {
					// except from 2xx response
					msg.classList.remove("success");
				}

				if (typeof data == "object") {
					msg.innerHTML = data.message;

					if (data.redirect != undefined) {
						window.location.href = data.redirect;
					}
				} else msg.innerHTML = data;
			} catch (err) {
				// network error or request not initiated
				console.log(err);

				msg.innerHTML = "Unable to connect to server";
			}
		});
	});

	document.querySelectorAll(".search-form form").forEach((form) => {
		form.querySelector("input[type='search']").addEventListener("input", function () {
			form.dispatchEvent(new Event("submit"));
		});

		form.addEventListener("submit", function (e) {
			e.preventDefault();

			const search = form.search.value.toLowerCase().trim();

			document.querySelectorAll(form.dataset.searchRecord).forEach((record) => {
				if (record.innerText.toLowerCase().indexOf(search) > -1)
					record.closest("tr").style.display = "table-row";
				else record.closest("tr").style.display = "none";
			});
		});
	});
});

function openAddDialog() {
	const dialog = document.querySelector("dialog.add-dialog");

	dialog?.showModal();
}

function openEditDialog(data) {
	const dialog = document.querySelector("dialog.edit-dialog");

	dialog?.showModal();

	const form = dialog.querySelector("form");
	if (!form) return;

	data = JSON.parse(data);
	for (let i in data) {
		form[i].value = data[i];
	}
}

function closeDialog(e, confirmation = false) {
	const dialog = e.target?.closest("dialog");

	if (!confirmation) return;

	if (confirm("Do you want to close it?")) {
		dialog?.close();

		dialog.querySelector("form")?.reset();
	}
}

async function deleteData(event, url, body, removeClosestElem = undefined) {
	const formData = new FormData();
	var data = [];
	body = JSON.parse(body);
	for (let i in body) {
		data.push(i + "=" + body[i]);
	}

	try {
		const res = await fetch(url, {
			method: "delete",
			body: data.join("&"),
			headers: {
				"content-type": "multipart/form-data",
			},
		});

		var data;
		if (res.headers.get("content-type") == "application/json") {
			data = await res.json();
		} else {
			data = await res.text();
		}

		if (res.ok) {
			// delete successfull

			if (removeClosestElem) {
				event.target?.closest(removeClosestElem)?.remove();
			}
		} else if (typeof data == "object") {
			// return object is other then 204 status which means error
			if (data.message) alert(data.message);
		} else {
			if (data) alert(data);
		}
	} catch (err) {
		// network error or request not initiated
		console.log(err);

		msg.innerHTML = "Unable to connect to server";
	}
}
