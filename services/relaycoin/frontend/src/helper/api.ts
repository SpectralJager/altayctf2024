export async function api<T>(url: string, metod?: string, params?: {[key: string]: any}): Promise<T> {
    const method = metod ? metod.toUpperCase() : 'GET'

    const res = await fetch('/api/' + url, {
        method: method,
        credentials: 'include',
        mode: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body:
            (method === 'POST' && params)
                ? JSON.stringify(params)
                : undefined,
    })

    let resJson

    try {
        resJson = await res.json()
    } catch (e) {
        alert('Произошла ошибка')
        throw e
    }

    if (!res.ok || resJson.error) {
        alert(resJson?.error ?? 'Произошла ошибка')
        throw {...res}
    }

    return resJson
}