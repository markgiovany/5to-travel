<body style="margin:0; padding:0; background-color:#f4f6f9; font-family: Arial, sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td align="center">

        <!-- CONTENEDOR -->
        <table width="600" style="background:#ffffff; border-radius:12px; overflow:hidden;">

          <!-- HEADER -->
          <tr>
            <td style="padding:20px; text-align:center; background:#ffffff;">
              <h1 style="margin:0; font-size:24px;">
                <img src="imagenes/brooking.png" alt="BookingEngineer" style="height:40px; vertical-align:middle;">
              </h1>
            </td>
          </tr>

          <!-- BANNER -->
          <tr>
            <td style="background: linear-gradient(90deg, #2F80ED, #27AE60, #F2C94C); padding:40px; color:white;">
              <h2 style="margin:0; font-size:26px;">Confirmación de reservación</h2>
              <p style="margin-top:10px;">Gracias por confiar en nosotros</p>
            </td>
          </tr>

          <!-- CONTENIDO -->
          <tr>
            <td style="padding:30px; color:#333;">

              <h2 style="margin-top:0;">Hola, {{nombre}}</h2>

              <p>Tu reservación ha sido procesada exitosamente. Aquí tienes los detalles:</p>

              <!-- DETALLES -->
              <table width="100%" style="margin-top:20px; border-collapse:collapse;">

                <tr>
                  <td style="padding:12px; border-bottom:1px solid #eee;">📅 Fecha</td>
                  <td style="padding:12px; border-bottom:1px solid #eee;"><strong>{{fecha}}</strong></td>
                </tr>

                <tr>
                  <td style="padding:12px; border-bottom:1px solid #eee;">👥 Personas</td>
                  <td style="padding:12px; border-bottom:1px solid #eee;"><strong>{{personas}}</strong></td>
                </tr>

                <tr>
                  <td style="padding:12px;">📍 Servicio</td>
                  <td style="padding:12px;"><strong>{{servicio}}</strong></td>
                </tr>

              </table>

              <div style="text-align:center; margin-top:30px;">
                <a href="#"
                   style="background: linear-gradient(90deg, #2F80ED, #27AE60);
                          color:white;
                          padding:14px 30px;
                          text-decoration:none;
                          border-radius:8px;
                          font-weight:bold;
                          display:inline-block;">
                  Ver mi reservación
                </a>
              </div>

              <p style="margin-top:35px; color:#777; text-align:center;">
                Si no te has suscrito a esta pagina, por favor ignora este correo.
              </p>

            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td style="background:#f4f6f9; padding:20px; text-align:center; font-size:12px; color:#777;">
              
              <footer class="py-4 border-top mt-5 bg-white text-center">
    <div class="container">
        <p class="text-muted mb-0 small">
            &copy; 2026 <strong>BookingEngineering</strong>. Todos los derechos reservados.
        </p>
    </div>
</footer>

            </td>
          </tr>

          <tr>
            <td style="height:6px; background: linear-gradient(90deg, #2F80ED, #27AE60, #F2C94C);"></td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>