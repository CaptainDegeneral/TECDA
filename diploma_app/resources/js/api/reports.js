import axios from 'axios';

const getReportsList = async (userId, page, searchValue) => {
    return await axios.get(
        route('report.index', {
            user_id: userId,
            page: page,
            search_value: searchValue,
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

const exportReport = async (reportId) => {
    try {
        const response = await axios.get(
            route('report.export', { report: reportId }),
            {
                responseType: 'blob',
            },
        );

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;

        let fileName = `Report_${reportId}_${new Date().toISOString().slice(0, 10)}.docx`;
        const contentDisposition = response.headers['content-disposition'];
        if (contentDisposition) {
            const fileNameStarMatch = contentDisposition.match(
                /filename\*=UTF-8''([^;]+)/i,
            );
            if (fileNameStarMatch && fileNameStarMatch[1]) {
                fileName = decodeURIComponent(fileNameStarMatch[1]);
            } else {
                const fileNameMatch =
                    contentDisposition.match(/filename="([^"]+)"/);
                if (fileNameMatch && fileNameMatch[1]) {
                    fileName = fileNameMatch[1];
                } else {
                    const fileNamePlainMatch =
                        contentDisposition.match(/filename=([^;]+)/);
                    if (fileNamePlainMatch && fileNamePlainMatch[1]) {
                        fileName = fileNamePlainMatch[1].trim();
                    }
                }
            }
        }

        link.setAttribute('download', fileName);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        return { success: true };
    } catch (error) {
        console.error('Ошибка при скачивании отчета:', error);
        throw error;
    }
};

export { createReport, exportReport, getReport, getReportsList };
