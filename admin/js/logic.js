document.addEventListener("DOMContentLoaded", function () {
  const contenedor = document.getElementById("product-selector");
  if (!contenedor) return;

  // Elementos del DOM
  const buscador = document.getElementById("products_seeker");
  const resultadosBusqueda = document.getElementById("search-results");
  const listaProductos = document.getElementById("products_list");
  const paginacionDiv = document.getElementById("products-pagination");
  const tagsContainer = document.getElementById("selected-products");
  const inputHidden = document.getElementById("selected-products-input");

  // Estado
  let paginaActual = 1;
  let totalPaginas = 1;
  let terminoBusqueda = "";
  let productosSeleccionados = []; // Array de objetos {id, title}

  // Inicializar: cargar productos seleccionados desde los tags existentes
  function inicializarSeleccionados() {    
    // Verificar que el input "inputHidden" tenga valores y de ser el caso ingresar los nuevos productos sin dejarlos de lado
    const selectedValues = inputHidden.value;
    if (Array.isArray(selectedValues) && 0 < selectedValues.length) {
      productosSeleccionados = selectedValues;
    }

    inputHidden.value = '';
  }
  inicializarSeleccionados();

  // Cargar primera página de productos
  cargarProductos(1, "");

  // -------- FUNCIONES PRINCIPALES --------

  // Cargar productos desde el servidor
  function cargarProductos(pagina, busqueda) {
    paginaActual = pagina;
    terminoBusqueda = busqueda;

    const data = new URLSearchParams({
      action: "get_products_for_metabox",
      nonce: miMetabox.nonce,
      pagina: pagina,
      busqueda: busqueda,
    });

    fetch(miMetabox.ajaxurl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: data,
    })
      .then((response) => response.json())
      .then((respuesta) => {
        if (respuesta.success) {
          renderizarProductos(respuesta.data.productos);
          // renderizarPaginacion(respuesta.data.total_paginas, respuesta.data.pagina_actual);
        } else {
          console.error("Error:", respuesta);
        }
      })
      .catch((error) => {
        console.error("Error de red:", error);
      });
  }

  // Renderizar la lista de productos
  function renderizarProductos(productos) {
    listaProductos.innerHTML = "";

    if (productos.length === 0) {
      listaProductos.innerHTML = "<p>No se encontraron productos.</p>";
      return;
    }

    productos.forEach((producto) => {
      const div = document.createElement("div");
      div.className = "producto-item";
      div.textContent = producto.title + " (ID: " + producto.id + ")";
      div.dataset.id = producto.id;
      div.dataset.title = producto.title;

      // Al hacer clic, agregar el producto a la selección
      div.addEventListener("click", function () {
        agregarProducto(producto.id, producto.title);
      });

      listaProductos.appendChild(div);
    });
  }

  // Agregar un producto a la selección
  function agregarProducto(id, title) {
    // Evitar duplicados
    if (productosSeleccionados.some((p) => p.id === id)) {
      alert("Este producto ya está seleccionado.");
      return;
    }

    productosSeleccionados.push({ id, title });
    renderizarTags();
    actualizarInputHidden();
    // Ocultar resultados de búsqueda
    resultadosBusqueda.style.display = "none";
    buscador.value = "";
    // Recargar la lista de productos (para actualizar la paginación y evitar mostrar el seleccionado)
    cargarProductos(paginaActual, terminoBusqueda);
  }

  // Eliminar un producto de la selección
  function eliminarProducto(id) {
    productosSeleccionados = productosSeleccionados.filter((p) => p.id !== id);
    renderizarTags();
    actualizarInputHidden();
  }

  // Renderizar los tags en el contenedor
  function renderizarTags() {
    // if (tagsContainer.children.length <= 0) {
        // }
        tagsContainer.innerHTML = "";

    productosSeleccionados.forEach((p) => {
      const span = document.createElement("span");
      span.className = "producto-tag";
      span.dataset.id = p.id;
      // span.dataset.title = p.title;
      span.innerHTML = `${p.title} (ID: ${p.id}) <button type="button" class="eliminar-producto" data-id="${p.id}">×</button>`;
      const btnEliminar = span.querySelector(".eliminar-producto");
      btnEliminar.addEventListener("click", function (e) {
        e.stopPropagation();
        eliminarProducto(p.id);
      });
      tagsContainer.appendChild(span);
    });
  }

  // Actualizar el input hidden con los IDs separados por comas
  function actualizarInputHidden() {
    const selectedValues = inputHidden.value;
    if (Array.isArray(selectedValues) && 0 < selectedValues.length) {
      productosSeleccionados = selectedValues;
    }

    inputHidden.value = JSON.stringify(productosSeleccionados);
  }

  // -------- EVENTOS --------

  // Búsqueda en tiempo real
  let timeoutBuscador;
  buscador.addEventListener("input", function () {
    const termino = this.value.trim();
    clearTimeout(timeoutBuscador);
    timeoutBuscador = setTimeout(() => {
      if (termino.length >= 2) {
        // Mostrar resultados de búsqueda en el dropdown
        realizarBusqueda(termino);
      } else {
        resultadosBusqueda.style.display = "none";
        // Si el buscador está vacío, volver a la lista normal
        if (termino === "") {
          cargarProductos(1, "");
        }
      }
    }, 300);
  });

  // Búsqueda con Fetch (para el autocompletado)
  function realizarBusqueda(termino) {
    const data = new URLSearchParams({
      action: "get_products_for_metabox",
      nonce: miMetabox.nonce,
      pagina: 1,
      busqueda: termino,
    });

    fetch(miMetabox.ajaxurl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: data,
    })
      .then((response) => response.json())
      .then((respuesta) => {
        if (respuesta.success) {
          const productos = respuesta.data.productos;
          resultadosBusqueda.innerHTML = "";
          if (productos.length === 0) {
            resultadosBusqueda.innerHTML =
              '<div class="resultado-item">No hay resultados</div>';
          } else {
            productos.forEach((p) => {
              const div = document.createElement("div");
              div.className = "resultado-item";
              div.textContent = p.title + " (ID: " + p.id + ")";
              div.addEventListener("click", function () {
                agregarProducto(p.id, p.title);
              });
              resultadosBusqueda.appendChild(div);
            });
          }
          resultadosBusqueda.style.display = "block";
        }
      })
      .catch((error) => {
        console.error("Error en búsqueda:", error);
      });
  }

  // Ocultar resultados al hacer clic fuera
  document.addEventListener("click", function (e) {
    if (!contenedor.contains(e.target)) {
      resultadosBusqueda.style.display = "none";
    }
  });
});
