 const URL_API = "http://localhost:8080/api/products"

const tablaPlatos = document.getElementById("tablaPlatos")
const formPlato = document.getElementById("formPlato")
const tituloFormulario = document.getElementById("tituloFormulario")
const btnCancelar = document.getElementById("btnCancelar")
const mensaje = document.getElementById("mensaje")

const campos = {
    id: document.getElementById("idPlato"),
    nombre: document.getElementById("nombre"),
    categoria: document.getElementById("categoria"),
    descripcion: document.getElementById("descripcion"),
    precio: document.getElementById("precio"),
    stock: document.getElementById("stock")
}

function avisar(texto, color) {
    mensaje.textContent = texto
    mensaje.className = `alert alert-${color}`
    setTimeout(() => mensaje.classList.add("d-none"), 3000)
}

function pintarTabla(platos) {
    let filas = ""
    for (const plato of platos) {
        filas += `<tr>
            <td>${plato.id}</td>
            <td>
                <strong class="text-gold">${plato.nombre}</strong>
                <div class="small text-secondary">${plato.descripcion}</div>
            </td>
            <td>${plato.categoria}</td>
            <td class="text-end">$${Number(plato.precio).toFixed(2)}</td>
            <td class="text-end">${plato.stock}</td>
            <td class="text-center text-nowrap">
                <button class="btn btn-sm btn-outline-gold" data-accion="editar" data-id="${plato.id}">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" data-accion="borrar" data-id="${plato.id}">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        </tr>`
    }
    tablaPlatos.innerHTML = filas
}

async function cargarPlatos() {
    try {
        const respuesta = await fetch(URL_API)
        const platos = await respuesta.json()
        pintarTabla(platos)
    } catch (error) {
        avisar("No se pudo conectar con el servidor PHP", "danger")
    }
}

function limpiarFormulario() {
    formPlato.reset()
    campos.id.value = ""
    tituloFormulario.textContent = "Nuevo plato"
}

async function llenarFormulario(id) {
    const respuesta = await fetch(`${URL_API}/${id}`)
    const plato = await respuesta.json()

    campos.id.value = plato.id
    campos.nombre.value = plato.nombre
    campos.categoria.value = plato.categoria
    campos.descripcion.value = plato.descripcion
    campos.precio.value = plato.precio
    campos.stock.value = plato.stock
    tituloFormulario.textContent = "Editar plato"
    campos.nombre.focus()
}

async function guardarPlato(evento) {
    evento.preventDefault()

    const plato = {
        nombre: campos.nombre.value,
        categoria: campos.categoria.value,
        descripcion: campos.descripcion.value,
        precio: Number(campos.precio.value),
        stock: Number(campos.stock.value)
    }

    const editando = campos.id.value !== ""
    const url = editando ? `${URL_API}/${campos.id.value}` : `${URL_API}/register`

    const respuesta = await fetch(url, {
        method: editando ? "PUT" : "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(plato)
    })

    if (respuesta.ok) {
        avisar(editando ? "Plato actualizado" : "Plato registrado", "success")
        limpiarFormulario()
        cargarPlatos()
    } else {
        avisar("Revisa los datos del plato", "danger")
    }
}

async function borrarPlato(id) {
    const confirmar = confirm("¿Deseas eliminar este plato de la carta?")
    if (!confirmar) return

    const respuesta = await fetch(`${URL_API}/${id}`, { method: "DELETE" })

    if (respuesta.ok) {
        avisar("Plato eliminado", "success")
        cargarPlatos()
    } else {
        avisar("No se pudo eliminar el plato", "danger")
    }
}

tablaPlatos.addEventListener("click", (evento) => {
    const boton = evento.target.closest("button")
    if (!boton) return

    if (boton.dataset.accion === "editar") {
        llenarFormulario(boton.dataset.id)
    }
    if (boton.dataset.accion === "borrar") {
        borrarPlato(boton.dataset.id)
    }
})

formPlato.addEventListener("submit", guardarPlato)
btnCancelar.addEventListener("click", limpiarFormulario)

cargarPlatos()
