import axios from 'axios';

const getReportsList = async (userId) => {
    return await axios.get(
        route('report.index', {
            user_id: userId,
        }),
    );
};

const getReport = async (id) => {
    return await axios.get(
        route('report.show', {
            id,
        }),
    );
};

const createReport = async (userId, data, name) => {
    return await axios.post(route('report.store'), {
        name: name,
        data: data,
        user_id: userId ?? null,
    });
};

export { createReport, getReport, getReportsList };
