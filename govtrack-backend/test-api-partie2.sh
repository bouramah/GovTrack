#!/bin/bash

# =========================================================================
# SCRIPT DE TEST COMPLET - API GOVTRACK PARTIE 2
# Instructions/Recommandations, Discussions, Pièces Jointes
# =========================================================================

BASE_URL="http://127.0.0.1:8000/api/v1"
TEMP_DIR="./temp_uploads"
mkdir -p $TEMP_DIR

# Couleurs pour l'affichage
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}================================================"
echo -e "🚀 TESTS API GOVTRACK - PARTIE 2"
echo -e "Instructions, Discussions, Pièces Jointes"
echo -e "================================================${NC}\n"

# Variables globales
ACCESS_TOKEN=""
USER_ID=""
PROJET_ID=""
TACHE_ID=""
TYPE_PROJET_ID=""

# Fonction pour afficher les résultats
show_result() {
    local status=$1
    local title=$2
    local response=$3

    if [[ $status -eq 200 || $status -eq 201 ]]; then
        echo -e "${GREEN}✅ $title${NC}"
    else
        echo -e "${RED}❌ $title (Status: $status)${NC}"
        echo -e "${YELLOW}Response: $response${NC}"
    fi
    echo
}

# Fonction pour extraire une valeur JSON
extract_json_value() {
    local json=$1
    local key=$2
    echo $json | grep -o "\"$key\":[^,}]*" | cut -d':' -f2 | tr -d '"' | xargs
}

# =================================================================
# 1. AUTHENTIFICATION
# =================================================================
echo -e "${BLUE}🔐 1. AUTHENTIFICATION${NC}"

login_response=$(curl -s -w "%{http_code}" -X POST \
  "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@govtrack.gov",
    "password": "password123"
  }')

status=${login_response: -3}
response=${login_response%???}

if [[ $status -eq 200 ]]; then
    ACCESS_TOKEN=$(extract_json_value "$response" "access_token")
    USER_ID=$(extract_json_value "$response" "id")
    echo -e "${GREEN}✅ Authentification réussie${NC}"
    echo -e "User ID: $USER_ID"
else
    echo -e "${RED}❌ Échec de l'authentification${NC}"
    exit 1
fi
echo

# =================================================================
# 2. TYPES DE PROJETS (SLA)
# =================================================================
echo -e "${BLUE}📋 2. GESTION DES TYPES DE PROJETS ET SLA${NC}"

type_response=$(curl -s -w "%{http_code}" -X POST \
  "$BASE_URL/type-projets" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nom": "Test API Urgence",
    "description": "Type de projet pour tests API avec délai court",
    "duree_previsionnelle_jours": 5,
    "description_sla": "Délai de 5 jours pour les tests urgents"
  }')

status=${type_response: -3}
response=${type_response%???}
TYPE_PROJET_ID=$(extract_json_value "$response" "id")

show_result $status "Création type de projet" "$response"

# =================================================================
# 3. PROJETS (INSTRUCTIONS/RECOMMANDATIONS)
# =================================================================
echo -e "${BLUE}📊 3. GESTION DES PROJETS${NC}"

projet_response=$(curl -s -w "%{http_code}" -X POST \
  "$BASE_URL/projets" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "titre": "Test API - Amélioration Système",
    "description": "Projet de test pour valider l'\''API",
    "type_projet_id": '"$TYPE_PROJET_ID"',
    "porteur_id": '"$USER_ID"',
    "donneur_ordre_id": '"$USER_ID"',
    "date_debut_previsionnelle": "2025-01-15"
  }')

status=${projet_response: -3}
response=${projet_response%???}
PROJET_ID=$(extract_json_value "$response" "id")

show_result $status "Création projet avec SLA automatique" "$response"

# Tableau de bord
dashboard_response=$(curl -s -w "%{http_code}" -X GET \
  "$BASE_URL/projets/tableau-bord" \
  -H "Authorization: Bearer $ACCESS_TOKEN")

status=${dashboard_response: -3}
response=${dashboard_response%???}

show_result $status "Tableau de bord" "$response"

# =================================================================
# 4. TÂCHES
# =================================================================
echo -e "${BLUE}✅ 4. GESTION DES TÂCHES${NC}"

if [[ -n "$PROJET_ID" ]]; then
    tache_response=$(curl -s -w "%{http_code}" -X POST \
      "$BASE_URL/taches" \
      -H "Authorization: Bearer $ACCESS_TOKEN" \
      -H "Content-Type: application/json" \
      -d '{
        "titre": "Test API - Analyse",
        "description": "Tâche de test pour analyser les besoins",
        "projet_id": '"$PROJET_ID"',
        "responsable_id": '"$USER_ID"',
        "date_debut_previsionnelle": "2025-01-15",
        "date_fin_previsionnelle": "2025-01-18"
      }')

    status=${tache_response: -3}
    response=${tache_response%???}
    TACHE_ID=$(extract_json_value "$response" "id")

    show_result $status "Création tâche" "$response"
fi

# =================================================================
# 5. PIÈCES JOINTES
# =================================================================
echo -e "${BLUE}📎 5. GESTION DES PIÈCES JOINTES${NC}"

echo "Test document content" > "$TEMP_DIR/test-document.txt"

if [[ -n "$PROJET_ID" ]]; then
    upload_response=$(curl -s -w "%{http_code}" -X POST \
      "$BASE_URL/projets/$PROJET_ID/pieces-jointes" \
      -H "Authorization: Bearer $ACCESS_TOKEN" \
      -F "fichier=@$TEMP_DIR/test-document.txt" \
      -F "description=Document de test" \
      -F "est_justificatif=true")

    status=${upload_response: -3}
    response=${upload_response%???}

    show_result $status "Upload justificatif" "$response"
fi

# =================================================================
# 6. DISCUSSIONS
# =================================================================
echo -e "${BLUE}💬 6. DISCUSSIONS COLLABORATIVES${NC}"

if [[ -n "$PROJET_ID" ]]; then
    discussion_response=$(curl -s -w "%{http_code}" -X POST \
      "$BASE_URL/projets/$PROJET_ID/discussions" \
      -H "Authorization: Bearer $ACCESS_TOKEN" \
      -H "Content-Type: application/json" \
      -d '{
        "message": "Test des discussions collaboratives"
      }')

    status=${discussion_response: -3}
    response=${discussion_response%???}

    show_result $status "Message discussion" "$response"
fi

# =================================================================
# 7. NETTOYAGE
# =================================================================
rm -rf $TEMP_DIR

echo -e "${GREEN}✅ Tests terminés avec succès !${NC}"
