
# Stage 1: Build aplikasi Vue
FROM node:22-alpine AS build

WORKDIR /app

# Cache instalasi dependency
COPY package.json package-lock.json ./

RUN npm ci

# Copy source frontend
COPY . .

# Gunakan relative API URL untuk Docker
ARG VITE_API_BASE_URL=/api
ENV VITE_API_BASE_URL=${VITE_API_BASE_URL}

# Build aplikasi Vue
RUN npm run build

# Stage 2: Jalankan menggunakan Nginx
FROM nginx:1.27-alpine

# Hapus konfigurasi default
RUN rm /etc/nginx/conf.d/default.conf

# Copy konfigurasi Nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy hasil build, bukan node_modules
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
