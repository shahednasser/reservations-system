<button class="{{isset($classes) ? $classes : ''}}" id="{{isset($id) ? $id : ''}}"
        name="{{isset($name) ? $name: ''}}" type="button">
    {{$text}}
    <span class="loading d-none">
          <svg xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg"
               xmlns:xlink="http://www.w3.org/1999/xlink" version="1.0" width="24" height="24"
               viewBox="0 0 128 128" xml:space="preserve">
              <rect x="0" y="0" width="100%" height="100%" fill="none" />
              <g>
                  <circle cx="16" cy="64" r="16" fill="{{isset($theme) && $theme === "dark" ? '#fff' : '#28a745'}}" fill-opacity="1"/>
                  <circle cx="16" cy="64" r="16" fill="{{isset($theme) && $theme === "dark" ? '#ececec' : '#70c483'}}" fill-opacity="0.67" transform="rotate(45,64,64)"/>
                  <circle cx="16" cy="64" r="16" fill="#a5dab1" fill-opacity="0.42" transform="rotate(90,64,64)"/>
                  <circle cx="16" cy="64" r="16" fill="#d4edda" fill-opacity="0.2" transform="rotate(135,64,64)"/>
                  <circle cx="16" cy="64" r="16" fill="#e6f5e9" fill-opacity="0.12" transform="rotate(180,64,64)"/>
                  <circle cx="16" cy="64" r="16" fill="#e6f5e9" fill-opacity="0.12" transform="rotate(225,64,64)"/>
                  <circle cx="16" cy="64" r="16" fill="#e6f5e9" fill-opacity="0.12" transform="rotate(270,64,64)"/>
                  <circle cx="16" cy="64" r="16" fill="#e6f5e9" fill-opacity="0.12" transform="rotate(315,64,64)"/>
                  <animateTransform attributeName="transform" type="rotate"
                                    values="0 64 64;315 64 64;270 64 64;225 64 64;180 64 64;135 64 64;90 64 64;45 64 64"
                                    calcMode="discrete" dur="720ms" repeatCount="indefinite">
                  </animateTransform>
              </g>
          </svg>
        </span>
</button>