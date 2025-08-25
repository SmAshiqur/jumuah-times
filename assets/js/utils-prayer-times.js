function Utils() {
    const currentDate= new Date();

    function padZero(value) {
        return String(value).padStart(2, "0");
    }

    // Array of month names
    const monthNames = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
    ];

    return {
        monthData: function () {
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear();

            // Array to store the month data
            const monthData = [];

            // Generate the month data
            for (let i = currentDate.getMonth(); i < 12; i++) {
                const label = `${monthNames[i]} ${currentYear}`;
                monthData.push({ monthname: monthNames[i], label, value: i });
            }

            return monthData;
        },

        getNextPreviousMonth: function (offset) {
            currentDate.setMonth(currentDate.getMonth() + 1 * offset);

            const month = currentDate.getMonth();
            const year = currentDate.getFullYear();

            return `${monthNames[month]} ${year}`
        },

        createError: function (status = 500, message = "an unknown error occurred") {
            const error = new Error(message);
            error.status = status;

            return error;
        }
    };
}

const utils = new Utils();
