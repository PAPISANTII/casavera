// ================================================
// CONFIGURACIÓN GENERAL DE CASA VERA
// Para cambiar precios o datos de contacto,
// edita solo este archivo. Se actualiza en toda la web.
// ================================================

const CASAVERA = {

  // --- CONTACTO ---
  email: "info@casaveraestudiantes.es",
  direccion: "Paseo Delicias 23, 30202 Cartagena",
  ciudad: "Cartagena, Murcia",

  // --- CONDICIONES GENERALES ---
  contratoMinimo: 4,
  fianza: "medio mes",
  formaPago: "transferencia bancaria",

  // --- PRECIOS (en € / mes) ---
  precios: {
    individual_balcon_banio_interior:       450,
    individual_sin_balcon_banio_interior:   440,
    individual_balcon_banio_exterior:       430,
    individual_sin_balcon_banio_exterior:   420,
    doble_grande_banio_interior:            650,
    doble_balcon_banio_exterior:            600,
    individual_planta_baja:                 400,
  },

  // --- FIANZAS (calculadas automáticamente: medio mes) ---
  fianzas: function() {
    const result = {};
    for (const tipo in this.precios) {
      result[tipo] = (this.precios[tipo] / 2).toFixed(0);
    }
    return result;
  }
};
