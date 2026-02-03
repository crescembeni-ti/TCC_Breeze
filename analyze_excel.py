import pandas as pd
import sys

try:
    df = pd.read_excel('/home/ubuntu/upload/CópiadeInventário(MapeamentodeEspécimes)(respostas).xlsx')
    # Identificar a coluna CAP pelo nome exato ou parcial
    cap_col_name = [c for c in df.columns if 'CAP' in c][0]
    print(f"Coluna identificada: {cap_col_name}")
    
    max_caps = 0
    for val in df[cap_col_name].dropna().astype(str):
        count = len(val.split(','))
        if count > max_caps:
            max_caps = count
    
    print(f"Máximo de CAPs: {max_caps}")
    print(f"Total de linhas: {len(df)}")
except Exception as e:
    print(f"Erro: {e}")
