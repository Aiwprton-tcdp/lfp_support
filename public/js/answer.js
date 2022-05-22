let deals = [], specialtyList = [], departmentList = [], dealsList = [], interviewsList = [], internshipList = [];
BX24.init(function(){
	// console.log("Инициализация завершена!", BX24.isAdmin()); 
	getSalesDepartment();
	getSpecialty();
	getDealsStatus();
	getContacts();
});

function getSalesDepartment(){
	BX24.callMethod(
		"crm.deal.userfield.list", 
		{
			filter: {
				"FIELD_NAME": "UF_CRM_1561882407"
			}
		}, 
		function(result) 
		{
			if(result.error()){
				console.error(result.error());
			}else{
				let select = document.getElementById("selectSalesDepartment"),
					items = result.data()[0].LIST;

				for(let i = 0; i < items.length; i++) {
					let textContent = items[i].VALUE,
						value = items[i].ID,
						el = document.createElement("option");
					el.textContent = textContent;
					el.value = value;
					select.appendChild(el);
				}
				select.selectedIndex = 0;
				departmentList = result.data()[0].LIST;
				getDeals();
			}
		}
	);
}

function getSpecialty(){
	BX24.callMethod(
		"crm.deal.userfield.list", 
		{
			filter: {
				"FIELD_NAME": "UF_CRM_1631508270396"
			}
		}, 
		function(result) 
		{
			let select = document.getElementById("selectSpecialty"),
					items = result.data()[0].LIST;

			for(let i = 0; i < items.length; i++) {
				let textContent = items[i].VALUE;
				if(!['Менеджер по продажам', 'Делопроизводитель', 'Руководитель отдела продаж'].includes(textContent))
					continue;

				let value = items[i].ID,
					el = document.createElement("option");
				el.textContent = textContent;
				el.value = value;
				select.appendChild(el);
			}
			select.selectedIndex = 0;
			specialtyList = result.data()[0].LIST;
			getDeals();
		}
	);
}

function getDealsStatus(){
	BX24.callMethod(
		"crm.status.list", 
		{
			// filter: {
			// 	"FIELD_NAME": "UF_CRM_1561882407"
			// }
		}, 
		function(result) 
		{
			if(result.error()){
				console.error(result.error());
			}else{
				dealsList = result.data();
			}
		}
	);
}

function getContacts(){
	// BX24.callMethod(
	// 	"crm.deal.userfield.list", 
	// 	{
	// 		filter: {
	// 			"FIELD_NAME": "UF_CRM_1561882407"
	// 		}
	// 	}, 
	// 	function(result) 
	// 	{
	// 		if(result.error()){
	// 			console.error(result.error());
	// 		}else{
	// 			let select = document.getElementById("selectSalesDepartment"),
	// 				items = result.data()[0].LIST;

	// 			for(let i = 0; i < items.length; i++) {
	// 				let textContent = items[i].VALUE,
	// 					value = items[i].ID,
	// 					el = document.createElement("option");
	// 				el.textContent = textContent;
	// 				el.value = value;
	// 				select.appendChild(el);
	// 			}
	// 			select.selectedIndex = 0;
	// 			departmentList = result.data()[0].LIST;
	// 			getDeals();
	// 		}
	// 	}
	// );
}

function getDeals(){
	let departmentID = $('#selectSalesDepartment').val(),
		specialtyID = $('#selectSpecialty').val(),
		div = document.getElementById("data");
	let	div2 = document.getElementById("data2");

	$(div).find('tbody').remove();
	$(div2).find('tbody').remove();
	BX24.callMethod("crm.deal.list", {
		order: {
			"ID": "desk"
		},
		filter: {
			"CATEGORY_ID": 16, 
			"STAGE_ID": ['C16:EXECUTING', 'C16:4', 'C16:5', 'C16:6', 'C16:7', 'C16:8', 'C16:FINAL_INVOICE'],
			"=UF_CRM_1561882407": departmentID,
			"=UF_CRM_1631508270396": specialtyID,
			// "><UF_CRM_1637841569953": data
		},
		select: ["ID", "TITLE", "UF_CRM_1631508270396", "UF_CRM_61164237F3C38", "UF_CRM_1561882407", "CONTACT", "STAGE_ID", "UF_CRM_1637841569953"]
	},function(res){
		if(res.error())
			console.error(res.error());
		else{
			let deals_x = res.data();

			for(indexDeal in deals_x){
				let datatime = "", newDiv = div;
				if(deals_x[indexDeal].STAGE_ID == "C16:6") {
					datatime = deals_x[indexDeal].UF_CRM_1637841569953 == "" ? "" : new Date(deals_x[indexDeal].UF_CRM_1637841569953).toLocaleString('ru', { hour12: false });
					newDiv = div2;
				}else{
					datatime = deals_x[indexDeal].UF_CRM_61164237F3C38 == "" ? "" : new Date(deals_x[indexDeal].UF_CRM_61164237F3C38).toLocaleString('ru', { hour12: false });
				}
				if(datatime == "") continue;
				let specialty = deals_x[indexDeal].UF_CRM_1631508270396 == null ? "" : deals_x[indexDeal].UF_CRM_1631508270396;
					department = deals_x[indexDeal].UF_CRM_1561882407 == null ? "" : deals_x[indexDeal].UF_CRM_1561882407,
					dealstatus = deals_x[indexDeal].STAGE_ID == null ? "" : deals_x[indexDeal].STAGE_ID;

					if(specialty != ""){ specialty = specialtyList.find(x => x.ID == specialty).length != 0 ? specialtyList.find(x => x.ID == specialty).VALUE : "";}
					if(department != ""){ department = departmentList.find(x => x.ID == department).length != 0 ? departmentList.find(x => x.ID == department).VALUE : "";}
					if(dealstatus != ""){ dealstatus = dealsList.find(x => x.STATUS_ID == dealstatus).length != 0 ? dealsList.find(x => x.STATUS_ID == dealstatus).NAME : "";}

					$(newDiv).append(`<tbody><tr>
						<td scope="col-sm"><a target="_blank" href="https://xn--d1ao9c.xn--p1ai/crm/deal/details/${deals_x[indexDeal].ID}/">${deals_x[indexDeal].TITLE}</a></td>
						<td scope="col-sm">${datatime}</td>
						<td scope="col-sm">${specialty}</td>
						<td scope="col-sm">${department}</td>
						<td scope="col-sm">${dealstatus}</td>
						<td scope="col-sm"><textarea name="comment" cols="20" rows="1"></textarea></td>
						</tr></tbody>`);
				// if(deals_x[indexDeal].UF_CRM_1631508270396 == specialtyList[indexSpecialty].ID) div.innerHTML += `${deals_x[indexDeal].TITLE} <---> ${deals_x[indexDeal].UF_CRM_61164237F3C38} <---> ${specialtyList[indexSpecialty].VALUE} <br>`;
				deals.push(deals_x[indexDeal]);
			}
			if(res.more())
				res.next();
		}
	});
}

$("#selectSpecialty").select2({
	closeOnSelect : false,
	// allowHtml: true,
	allowClear: true,
	tags: true // создает новые опции на лету
});

$("#selectSalesDepartment").select2({
	closeOnSelect : false,
	// allowHtml: true,
	allowClear: true,
	tags: true // создает новые опции на лету
});