import pandas as pd
import math
import sys
import os

# Simular ambiente Laravel para obter conexão se possível, 
# mas aqui vamos gerar o SQL diretamente para o usuário executar ou eu tentar rodar via sqlite se for o caso.
# Como não tenho acesso ao DB real, vou gerar um arquivo SQL de UPDATE.

try:
    df = pd.read_excel('/home/ubuntu/upload/CópiadeInventário(MapeamentodeEspécimes)(respostas).xlsx')
    cap_col = [c for c in df.columns if 'CAP' in c][0]
    lat_long_col = [c for c in df.columns if 'Latitude' in c][0]
    
    sql_updates = []
    
    for index, row in df.iterrows():
        cap_val = str(row[cap_col])
        coords = str(row[lat_long_col])
        
        if cap_val == 'nan' or coords == 'nan':
            continue
            
        try:
            lat, lon = coords.split(',')
            lat = float(lat.strip())
            lon = float(lon.strip())
        except:
            continue
            
        caps = [c.strip() for c in cap_val.split(',')]
        update_parts = []
        
        for i, c in enumerate(caps):
            if i >= 20: break
            try:
                c_float = float(c)
                d_float = round(c_float / 3.14159, 2)
                update_parts.append(f"cap{i+1} = {c_float}, dap{i+1} = {d_float}")
            except:
                continue
        
        if update_parts:
            sql = f"UPDATE trees SET {', '.join(update_parts)} WHERE ABS(latitude - {lat}) < 0.0001 AND ABS(longitude - {lon}) < 0.0001;"
            sql_updates.append(sql)

    with open('import_caps.sql', 'w') as f:
        f.write('\n'.join(sql_updates))
    
    print(f"Gerado import_caps.sql com {len(sql_updates)} comandos de atualização.")

except Exception as e:
    print(f"Erro: {e}")
