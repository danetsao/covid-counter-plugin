document.getElementById('covid-counter-analytics-download-csv-button').addEventListener('click', () => {
	fetch(`${covidCounterAnalytics.covidCounterApiBaseUrl}/analytics`)
		.then(response => response.json())
		.then(analytics => {
			let csv = 'month,day,hour,location,number_of_entries\n'

			analytics.forEach(analytic => csv += `${analytic.month},${analytic.day},${analytic.hour},${analytic.location},${analytic.number_of_entries}\n`)

			const a = document.createElement('a')

			a.href = URL.createObjectURL(new Blob([csv], {type: 'text/csv'}))
			a.download = 'covid-counter-analytics.csv'
			
			document.body.appendChild(a)

			a.click()
			a.remove()
		})
})
